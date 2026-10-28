#!/usr/bin/env bash
set -e

# ==============================================================================
# Mutamark ERP - One-Click Production Deployment Script
# Domain: https://mutamark.wolinet.com/
# ==============================================================================

APP_DIR="${1:-/home/mutamark.wolinet.com/public_html}"
cd "$APP_DIR"

echo "=========================================================="
echo "🚀 Starting Mutamark ERP Deployment at $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================================="

# 1. Fetch & force-sync cleanly from origin/main
echo "📥 [1/6] Syncing latest commits from GitHub origin/main..."
git fetch origin main
git checkout -f main
git reset --hard origin/main

# 2. Composer dump-autoload & dependencies
echo "📦 [2/6] Optimizing composer autoload..."
composer dump-autoload -o --no-interaction

# 3. Database migrations & seeders
echo "🗄️ [3/6] Running migrations and syncing niche enterprise users & modules..."
php artisan migrate --force
php artisan db:seed --class=MutamarkNicheUsersSeeder --force

# 4. Clear and rebuild all application caches
echo "⚡ [4/6] Purging and rebuilding production caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Reset storage links & public asset symlinks
echo "🔗 [5/6] Verifying storage and asset links..."
php artisan storage:link --force 2>/dev/null || true
mkdir -p "$APP_DIR/storage/framework/cache/data"
mkdir -p "$APP_DIR/storage/framework/views"
mkdir -p "$APP_DIR/storage/framework/sessions"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# 6. Flush OpenLiteSpeed / PHP opcode cache & restart worker
echo "🔄 [6/6] Reloading OpenLiteSpeed and PHP workers..."
mkdir -p "$APP_DIR/tmp"
touch "$APP_DIR/tmp/restart.txt"

# Kill any stale stuck lsphp worker processes to reload opcode cache immediately
pkill -9 -f lsphp 2>/dev/null || true

# Graceful restart of OpenLiteSpeed if running with systemctl
if command -v systemctl &> /dev/null; then
    systemctl restart lsws 2>/dev/null || true
fi

echo "=========================================================="
echo "✅ Mutamark ERP Deployment Complete at $(date '+%Y-%m-%d %H:%M:%S')!"
echo "=========================================================="
