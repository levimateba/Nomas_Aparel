#!/bin/bash
# Run on Truhost cPanel Terminal after uploading/cloning the app.
# Usage: cd ~/nomas-apparel && ./scripts/truhost-post-deploy.sh

set -e

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "==> Nomas Apparel — Truhost post-deploy"
echo "    Directory: $APP_DIR"

if [[ ! -f .env ]]; then
    if [[ -f .env.production.example ]]; then
        cp .env.production.example .env
        echo "Created .env from .env.production.example — edit DB credentials before continuing."
        exit 1
    else
        echo "ERROR: .env not found. Copy .env.production.example to .env and configure it."
        exit 1
    fi
fi

if grep -q '^APP_KEY=$' .env || grep -q '^APP_KEY=$' .env 2>/dev/null; then
    php artisan key:generate --force
    echo "Generated APP_KEY"
fi

if [[ -d vendor ]]; then
    echo "==> Optimizing Composer autoloader"
    composer dump-autoload --optimize --no-dev 2>/dev/null || true
else
    echo "==> Installing Composer dependencies"
    composer install --no-dev --optimize-autoloader --no-interaction
fi

echo "==> Running migrations"
php artisan migrate --force

echo "==> Linking storage"
php artisan storage:link 2>/dev/null || true

echo "==> Caching config, routes, views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Setting permissions"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "Done. Visit https://nomas.elgontect.co.ke/up to verify."
echo "Admin: https://nomas.elgontect.co.ke/admin"
echo "Change the default admin password after first login."
