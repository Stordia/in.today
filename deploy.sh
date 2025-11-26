#!/bin/bash
set -e

cd /var/www/vhosts/in.today/dev.in.today

echo "--- STEP 1: Node.js dependencies ---"
/var/www/vhosts/in.today/.nodenv/shims/npm install

echo "--- STEP 2: PHP dependencies (composer install) ---"
/var/www/vhosts/in.today/.phpenv/shims/composer install --no-dev --optimize-autoloader

echo "--- STEP 3: Vite build (Tailwind + JS) ---"
/var/www/vhosts/in.today/.nodenv/shims/npm run build

echo "--- STEP 4: Laravel cache clear ---"
/var/www/vhosts/in.today/.phpenv/shims/php artisan config:clear
/var/www/vhosts/in.today/.phpenv/shims/php artisan route:clear
/var/www/vhosts/in.today/.phpenv/shims/php artisan view:clear

echo "✅ DEPLOYMENT COMPLETED FOR in.today"
