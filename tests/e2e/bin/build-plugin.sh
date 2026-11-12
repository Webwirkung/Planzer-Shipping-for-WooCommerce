#!/usr/bin/env bash
# Builds the plugin the way a release does (webpack.build.mix.js file list,
# composer without dev packages, admin assets) into tests/e2e/.build.
# Node and Composer run in Docker, so the host needs nothing but Docker.
set -euo pipefail

E2E_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_DIR="$(cd "$E2E_DIR/../.." && pwd)"
OUT="$E2E_DIR/.build/wc-planzer-shipping"
CACHE="$E2E_DIR/.cache"
USER_ID="$(id -u):$(id -g)"

rm -rf "$OUT"
mkdir -p "$OUT/resources" "$CACHE/npm" "$CACHE/composer"

cp "$PLUGIN_DIR/wc-planzer-shipping.php" "$PLUGIN_DIR"/readme*.txt "$PLUGIN_DIR/composer.json" "$OUT/"
cp -r "$PLUGIN_DIR/inc" "$PLUGIN_DIR/src" "$OUT/"
cp -r "$PLUGIN_DIR/resources/views" "$PLUGIN_DIR/resources/lang" "$OUT/resources/"

echo "==> composer install --no-dev"
docker run --rm -u "$USER_ID" -e COMPOSER_HOME=/tmp/composer \
  -v "$CACHE/composer:/tmp/composer" -v "$OUT:/app" -w /app \
  composer:2 install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-reqs
rm -f "$OUT/composer.json" "$OUT/composer.lock"

echo "==> admin assets (laravel-mix 4)"
# package.json has no lock file and npm resolves webpack 3, which laravel-mix 4
# cannot run with. The override only applies to this throwaway copy.
docker run --rm -u "$USER_ID" -e HOME=/tmp \
  -v "$CACHE/npm:/tmp/.npm" -v "$PLUGIN_DIR:/src:ro" -v "$OUT:/out" \
  node:16-bullseye sh -ec '
    mkdir /tmp/b && cd /src && cp -r package.json webpack.mix.js webpack resources /tmp/b/ && cd /tmp/b
    node -e "const p=require(\"./package.json\");p.overrides={webpack:\"^4.47.0\"};p.devDependencies[\"webpack-cli\"]=\"^3.3.12\";require(\"fs\").writeFileSync(\"package.json\",JSON.stringify(p))"
    npm install --no-audit --no-fund --loglevel=error >/dev/null
    npm run production >/tmp/build.log 2>&1 || { cat /tmp/build.log; exit 1; }
    cp -r dist /out/dist'

mkdir -p "$OUT/vendor/mpdf/mpdf/tmp" && chmod 777 "$OUT/vendor/mpdf/mpdf/tmp"
echo "==> built $OUT"
