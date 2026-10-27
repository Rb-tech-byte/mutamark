#!/usr/bin/env bash
set -e

# ==============================================================================
# Mutamark ERP - CyberPanel Initial Production Setup Script
# Domain: https://mutamark.wolinet.com/
# ==============================================================================

APP_DIR="${1:-/home/mutamark.wolinet.com/public_html}"
cd "$APP_DIR"

echo "🚀 Starting Initial Production Setup for Mutamark ERP at $APP_DIR..."

# 1. Ensure .env exists
if [ ! -f "$APP_DIR/.env" ]; then
    if [ -f "$APP_DIR/deploy/.env.production.example" ]; then
        echo "📝 Creating initial .env from template..."
        cp "$APP_DIR/deploy/.env.production.example" "$APP_DIR/.env"
    else
        echo "⚠️ No .env template found. Please create $APP_DIR/.env manually."
        exit 1
    fi
fi

# 2. Set secure permissions
echo "🔒 Setting permissions..."
chmod 644 "$APP_DIR/.env"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/public"

# Detect CyberPanel site owner
SITE_USER=$(stat -c '%U' "$APP_DIR" 2>/dev/null || echo "mutam9314")
if [ "$SITE_USER" != "root" ] && [ -n "$SITE_USER" ]; then
    chown -R "$SITE_USER:$SITE_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
fi

# 3. Mark as installed so CheckInstallation middleware allows login
mkdir -p "$APP_DIR/storage"
if [ ! -f "$APP_DIR/storage/installed" ]; then
    echo "install $(date '+%Y-%m-%d %H:%M:%S')" > "$APP_DIR/storage/installed"
fi

# 4. Generate App Key if missing
if ! grep -q "APP_KEY=base64:" "$APP_DIR/.env"; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# 5. Install composer dependencies (optimized for production)
echo "📦 Installing production composer packages..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6. Create storage symlink
echo "🔗 Linking public storage..."
php artisan storage:link --force || true

# 7. Run database migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# 8. Optimize & build production caches
echo "⚡ Optimizing configuration, routes & views..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Restart OpenLiteSpeed / PHP process on CyberPanel
echo "🔄 Reloading LiteSpeed web server process..."
mkdir -p "$APP_DIR/tmp"
touch "$APP_DIR/tmp/restart.txt" 2>/dev/null || true
if command -v systemctl &> /dev/null; then
    systemctl restart lsws 2>/dev/null || true
fi

echo "✅ Production Setup Complete for https://mutamark.wolinet.com/!"
