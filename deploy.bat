@echo off
setlocal enabledelayedexpansion

echo ==========================================================
echo Starting Mutamark ERP Windows Deployment
echo ==========================================================

:: 1. Sync latest from Git
echo [1/5] Pulling latest code from origin/main...
git fetch origin main
git checkout -f main
git reset --hard origin/main

:: 2. Composer autoload
echo [2/5] Optimizing composer autoload...
call composer dump-autoload -o --no-interaction

:: 3. Database migrations and seeders
echo [3/5] Running database migrations and updating module tables...
call php artisan migrate --force
call php artisan db:seed --class=MutamarkClearingForwardingSeeder --force
call php artisan db:seed --class=MutamarkProcureToPaySeeder --force
call php artisan db:seed --class=MutamarkNicheUsersSeeder --force

:: 4. Cache management
echo [4/5] Clearing and optimizing caches...
call php artisan optimize:clear
call php artisan permission:cache-reset
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache

:: 5. Storage link
echo [5/5] Verifying storage link...
call php artisan storage:link --force

echo ==========================================================
echo Mutamark ERP Deployment Complete!
echo ==========================================================

