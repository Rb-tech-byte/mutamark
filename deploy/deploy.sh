#!/usr/bin/env bash
set -e

# ==============================================================================
# Mutamark ERP - Continuous Deployment (CD) Script
# Domain: https://mutamark.wolinet.com/
# ==============================================================================

APP_DIR="${1:-/home/mutamark.wolinet.com/public_html}"
cd "$APP_DIR"

echo "🔄 Deploying Mutamark ERP at $(date '+%Y-%m-%d %H:%M:%S')..."

# 1. Pull latest code from main
echo "📥 Pulling latest commits from git..."
git checkout -- storage/app/public/.gitignore 2>/dev/null || true
git pull origin main

# 2. Install composer dependencies (no-dev, optimized)
echo "📦 Updating composer packages..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 3. Run database migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# 4. Clear and rebuild production caches
echo "⚡ Rebuilding production caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4b. Ensure storage link and media synchronization
php artisan storage:link --force || true
mkdir -p "$APP_DIR/storage/app/public/media"
mkdir -p "$APP_DIR/public/storage/media"
cp -n "$APP_DIR/public/"*.png "$APP_DIR/storage/app/public/media/" 2>/dev/null || true
cp -n "$APP_DIR/public/"*.ico "$APP_DIR/storage/app/public/media/" 2>/dev/null || true
cp -n "$APP_DIR/public/"*.png "$APP_DIR/public/storage/media/" 2>/dev/null || true
cp -n "$APP_DIR/public/"*.ico "$APP_DIR/public/storage/media/" 2>/dev/null || true

ln -sfn public/build "$APP_DIR/build" 2>/dev/null || true
ln -sfn public/images "$APP_DIR/images" 2>/dev/null || true
ln -sfn public/assets "$APP_DIR/assets" 2>/dev/null || true
ln -sfn public/js "$APP_DIR/js" 2>/dev/null || true

# 5. Ensure permissions remain correct
chmod 644 "$APP_DIR/.env" 2>/dev/null || true
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# 6. Restart OpenLiteSpeed / PHP worker
mkdir -p "$APP_DIR/tmp"
touch "$APP_DIR/tmp/restart.txt" 2>/dev/null || true
if command -v systemctl &> /dev/null; then
    systemctl restart lsws 2>/dev/null || true
fi

echo "✅ Deployment successful at $(date '+%Y-%m-%d %H:%M:%S')!"
