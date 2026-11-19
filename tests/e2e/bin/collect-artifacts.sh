#!/usr/bin/env bash
# Collects logs and captured outputs after a failed run into tests/e2e/artifacts.
set -uo pipefail
cd "$(dirname "$0")/.."
OUT=artifacts
mkdir -p "$OUT/csv" "$OUT/wc-logs"
docker compose logs --no-color > "$OUT/docker-compose.log" 2>&1
docker compose exec -T wordpress cat wp-content/debug.log > "$OUT/debug.log" 2>/dev/null
docker compose exec -T wordpress sh -c 'tar -C wp-content/uploads/wc-logs -cf - .' 2>/dev/null | tar -C "$OUT/wc-logs" -xf - 2>/dev/null
docker compose exec -T sftp sh -c 'tar -C /home/WooCommerce/Eingang -cf - .' 2>/dev/null | tar -C "$OUT/csv" -xf - 2>/dev/null
curl -s "http://localhost:${PLANZER_E2E_MAILPIT_PORT:-8195}/api/v1/messages" > "$OUT/mailpit-messages.json"
exit 0
