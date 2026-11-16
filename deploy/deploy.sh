#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BRANCH="${DEPLOY_BRANCH:-main}"
APP_URL="${APP_URL:-https://mutamark.wolinet.com}"
SEED_FLAG=false

for arg in "$@"; do
  case "$arg" in
    --seed) SEED_FLAG=true ;;
  esac
done

log() {
  echo ""
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"
}

cd "$APP_DIR"
log "Starting deployment in $APP_DIR (branch=$BRANCH, url=$APP_URL)"

# Detect CyberPanel vHost owner from parent directory (e.g., /home/mutamark.wolinet.com)
VHOST_OWNER="$(stat -c '%U' "$(dirname "$APP_DIR")" 2>/dev/null || echo '')"

php artisan down --retry=60 >/dev/null 2>&1 || true
log "Maintenance mode enabled."

log "Fetching and syncing git origin/$BRANCH..."
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

log "Clearing stale bootstrap cache..."
rm -f bootstrap/cache/*.php

log "Installing Composer dependencies (--no-dev --optimize-autoloader)..."
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

log "Skipping npm build (prebuilt public/build/* active)."

log "Running database migrations..."
php artisan migrate --force

if [ "$SEED_FLAG" = true ]; then
  log "Running idempotent database seeders..."
  php artisan db:seed --class=CFCustomsRulesSeeder --force
fi

log "Linking public/storage and verifying runtime directories..."
php artisan storage:link >/dev/null 2>&1 || true
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

log "Optimizing Laravel caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart >/dev/null 2>&1 || true

log "Bringing application online..."
php artisan up >/dev/null 2>&1 || true

if [ -n "$VHOST_OWNER" ] && [ "$VHOST_OWNER" != "root" ]; then
  log "Restoring CyberPanel/vHost ownership ($VHOST_OWNER:$VHOST_OWNER, docRoot group nobody)..."
  chown -R "${VHOST_OWNER}:${VHOST_OWNER}" "$APP_DIR"
  chgrp nobody "$APP_DIR/public" "$APP_DIR" 2>/dev/null || true
  chmod -R 775 storage bootstrap/cache
fi

log "Reloading OpenLiteSpeed so .htaccess & PHP caches refresh cleanly..."
systemctl reload lsws >/dev/null 2>&1 || /usr/local/lsws/bin/lswsctrl reload >/dev/null 2>&1 || true

log "Running HTTP health check on $APP_URL/..."
HTTP_CODE="$(curl -s -o /dev/null -w '%{http_code}' -L "$APP_URL/" || echo '000')"
echo "HTTP Status from $APP_URL/: $HTTP_CODE"

log "Deployment completed cleanly for $APP_URL."
