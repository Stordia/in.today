#!/bin/bash
set -e

# --- Βασικές διαδρομές ---
APP_ROOT="/var/www/vhosts/in.today"
APP_DIR="$APP_ROOT/dev.in.today"

# --- phpenv / nodenv περιβάλλον ---
export HOME="$APP_ROOT"
export PHPENV_ROOT="$HOME/.phpenv"
export NODENV_ROOT="$HOME/.nodenv"
export PATH="$PHPENV_ROOT/shims:$PHPENV_ROOT/bin:$NODENV_ROOT/shims:$NODENV_ROOT/bin:$PATH"

cd "$APP_DIR"

echo "=== in.today deploy start ==="
echo "PWD: $(pwd)"
echo "PHP:  $(php -v | head -n 1 || echo 'php not found')"
echo "Comp: $(composer -V || echo 'composer not found')"
echo "Node: $(node -v || echo 'node not found')"
echo "NPM:  $(npm -v || echo 'npm not found')"

echo "--- STEP 1: PHP dependencies (composer install) ---"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "--- STEP 2: Node.js dependencies (npm install) ---"
npm install

echo "--- STEP 3: Vite build (Tailwind + JS) ---"
npm run build

echo "--- STEP 4: Laravel cache clear ---"
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ DEPLOYMENT COMPLETED FOR in.today"
