#!/bin/bash

# Nomas Apparel — Deployment Script
# For VPS with root access. For Truhost cPanel, use TRUHOST_DEPLOYMENT.md instead.

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

APP_PATH="${APP_PATH:-/home/YOUR_CPANEL_USERNAME/nomas-apparel}"
REPO_URL="${REPO_URL:-https://github.com/levimateba/Nomas_Aparel.git}"
BRANCH="${BRANCH:-main}"
BACKUP_DIR="${BACKUP_DIR:-/home/YOUR_CPANEL_USERNAME/backups/nomas-apparel}"
APP_URL="${APP_URL:-https://nomas.elgontect.co.ke}"

print_header() {
    echo -e "\n${GREEN}======================================${NC}"
    echo -e "${GREEN}$1${NC}"
    echo -e "${GREEN}======================================${NC}\n"
}

print_error() { echo -e "${RED}[ERROR] $1${NC}"; }
print_success() { echo -e "${GREEN}[✓] $1${NC}"; }
print_warning() { echo -e "${YELLOW}[WARNING] $1${NC}"; }

if [[ ! -d "$APP_PATH" ]]; then
    print_error "App path not found: $APP_PATH"
    exit 1
fi

print_header "Starting Nomas Apparel Deployment → $APP_URL"

print_header "Step 1: Creating Backup"
mkdir -p "$BACKUP_DIR"
BACKUP_DATE=$(date +%Y-%m-%d_%H-%M-%S)
tar -czf "$BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz" \
    "$APP_PATH/storage" \
    "$APP_PATH/database" \
    "$APP_PATH/.env" 2>/dev/null || true
print_success "File backup created"

print_header "Step 2: Pulling Latest Code"
cd "$APP_PATH"
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"
print_success "Code updated"

print_header "Step 3: Installing Dependencies"
composer install --no-dev --optimize-autoloader --no-interaction
if command -v npm &>/dev/null; then
    npm ci --silent 2>/dev/null || npm install --silent
    npm run build
fi
print_success "Dependencies installed"

print_header "Step 4: Running Migrations"
php artisan migrate --force
print_success "Migrations completed"

print_header "Step 5: Optimizing Application"
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

print_header "Step 6: Setting Permissions"
chmod -R 775 "$APP_PATH/storage" "$APP_PATH/bootstrap/cache"
print_success "Permissions set"

print_header "Step 7: Health Check"
if curl -sf "$APP_URL/up" >/dev/null 2>&1; then
    print_success "Health check passed ($APP_URL/up)"
else
    print_warning "Could not reach $APP_URL/up — verify DNS/SSL after deploy"
fi

print_header "Deployment Completed Successfully ✓"
echo -e "${GREEN}Live at: $APP_URL${NC}\n"
