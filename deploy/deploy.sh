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
