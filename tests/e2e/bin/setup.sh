#!/usr/bin/env bash
# Starts the environment and installs WordPress + WooCommerce (latest) + the built plugin.
# PLANZER_E2E_HPOS=1 (default) uses HPOS order tables, 0 uses legacy post storage.
set -euo pipefail
cd "$(dirname "$0")/.."

PORT="${PLANZER_E2E_PORT:-8190}"
HPOS="${PLANZER_E2E_HPOS:-1}"
wp() { docker compose exec -T -u www-data wordpress wp "$@"; }

[ -d .build/wc-planzer-shipping ] || bin/build-plugin.sh

docker compose up -d --build --wait
# The build recreates the plugin folder; restart so the bind mount sees the new one.
docker compose restart wordpress >/dev/null

for _ in $(seq 1 30); do wp core is-installed 2>/dev/null && break; wp db check >/dev/null 2>&1 && break; sleep 1; done

if ! wp core is-installed 2>/dev/null; then
  wp core install --url="http://localhost:$PORT" --title="Planzer E2E" \
    --admin_user=admin --admin_password=password --admin_email=admin@example.test --skip-email
fi
wp rewrite structure '/%postname%/' --hard >/dev/null
wp plugin install woocommerce --activate
wp plugin activate wc-planzer-shipping
# Core language packs, so WPLANG can switch the PDF language (S83).
wp language core install de_DE fr_FR it_IT >/dev/null

# Through WooCommerce's own command: setting the option directly is undone by WooCommerce
# on the next admin request for a shop without orders.
wp option update planzer_e2e_hpos "$HPOS" >/dev/null
if [ "$HPOS" = "1" ]; then
  wp wc hpos enable >/dev/null
else
  wp wc hpos disable >/dev/null
fi
wp option update woocommerce_custom_orders_table_data_sync_enabled no >/dev/null

wp eval-file /e2e/fixtures/php/seed.php
curl -s -o /dev/null "http://localhost:$PORT/wp-admin/" || true
STORAGE=$(wp eval 'echo \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? 1 : 0;')
[ "$STORAGE" = "$HPOS" ] || { echo "Order storage is HPOS=$STORAGE, expected HPOS=$HPOS" >&2; exit 1; }
echo "==> WordPress $(wp core version), WooCommerce $(wp plugin get woocommerce --field=version), HPOS=$HPOS"
echo "==> http://localhost:$PORT/wp-admin (admin / password), Mailpit http://localhost:${PLANZER_E2E_MAILPIT_PORT:-8195}"
