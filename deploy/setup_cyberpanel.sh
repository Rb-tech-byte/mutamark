#!/usr/bin/env bash
set -e

# ==============================================================================
# Mutamark ERP - CyberPanel Initial Production Setup Script
# Domain: https://mutamark.wolinet.com/
# ==============================================================================

APP_DIR="${1:-/home/mutamark.wolinet.com/public_html}"
cd "$APP_DIR"

echo "🚀 Starting Production Setup for Mutamark ERP at $APP_DIR..."

# 1. Ensure .env exists and has production credentials
if [ ! -f "$APP_DIR/.env" ]; then
    if [ -f "$APP_DIR/deploy/.env.production.example" ]; then
        echo "📝 Creating initial .env from template..."
        cp "$APP_DIR/deploy/.env.production.example" "$APP_DIR/.env"
    else
        echo "⚠️ No .env template found. Please create $APP_DIR/.env manually."
        exit 1
    fi
fi

# 2. Update Database credentials in .env
echo "🔧 Configuring production database credentials in .env..."
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=muta_muta/' "$APP_DIR/.env"
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=muta_muta/' "$APP_DIR/.env"
sed -i 's|^DB_PASSWORD=.*|DB_PASSWORD="++hL2ti@mDb7%6nx"|' "$APP_DIR/.env"
sed -i 's|^APP_URL=.*|APP_URL=https://mutamark.wolinet.com|' "$APP_DIR/.env"
sed -i 's/^APP_ENV=.*/APP_ENV=production/' "$APP_DIR/.env"
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' "$APP_DIR/.env"

# 3. Set secure permissions early
echo "🔒 Setting directory permissions..."
mkdir -p "$APP_DIR/scratch" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/tmp"
chmod 644 "$APP_DIR/.env"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/public" "$APP_DIR/scratch" "$APP_DIR/tmp" 2>/dev/null || true

# Detect CyberPanel site owner
SITE_USER=$(stat -c '%U' "$APP_DIR" 2>/dev/null || echo "mutam9314")
if [ "$SITE_USER" != "root" ] && [ -n "$SITE_USER" ]; then
    chown -R "$SITE_USER:$SITE_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/scratch" "$APP_DIR/tmp" 2>/dev/null || true
fi

# 4. Mark as installed so CheckInstallation middleware allows access
mkdir -p "$APP_DIR/storage"
if [ ! -f "$APP_DIR/storage/installed" ]; then
    echo "install $(date '+%Y-%m-%d %H:%M:%S')" > "$APP_DIR/storage/installed"
fi

# 5. Install composer packages FIRST (required before artisan can run)
echo "📦 Installing production composer packages..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6. Generate App Key if missing
if ! grep -q "APP_KEY=base64:" "$APP_DIR/.env"; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# 7. Create storage symlink
echo "🔗 Linking public storage..."
php artisan storage:link --force || true

# 7b. Synchronize media assets and create direct root symlinks
echo "🖼️ Synchronizing media assets & static symlinks..."
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

# 8. Run database migrations & seed all modules and initial data
echo "🗄️ Running migrations (creating all tables)..."
php artisan migrate --force

echo "🌱 Seeding default roles, enterprise modules & sample data..."
php artisan db:seed --force

# 9. Optimize & build production caches
echo "⚡ Optimizing configuration, routes & views..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Restart OpenLiteSpeed / PHP process on CyberPanel
echo "🔄 Reloading LiteSpeed web server process..."
mkdir -p "$APP_DIR/tmp"
touch "$APP_DIR/tmp/restart.txt" 2>/dev/null || true
if command -v systemctl &> /dev/null; then
    systemctl restart lsws 2>/dev/null || true
fi

echo "✅ Production Setup & Database Seeding Complete for https://mutamark.wolinet.com/!"
