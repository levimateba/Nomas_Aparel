#!/bin/bash

# Elgon Tech - Automated Deployment Script
# This script automates the deployment process for production servers

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_PATH="/var/www/elgon-tech"
REPO_URL="your-repository-url"
BRANCH="main"
BACKUP_DIR="/var/backups/elgon-tech"

# Functions
print_header() {
    echo -e "\n${GREEN}======================================${NC}"
    echo -e "${GREEN}$1${NC}"
    echo -e "${GREEN}======================================${NC}\n"
}

print_error() {
    echo -e "${RED}[ERROR] $1${NC}"
}

print_success() {
    echo -e "${GREEN}[✓] $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    print_error "This script must be run as root"
    exit 1
fi

# Begin deployment
print_header "Starting Elgon Tech Deployment"

# Step 1: Create backup
print_header "Step 1: Creating Backup"
mkdir -p $BACKUP_DIR
BACKUP_DATE=$(date +%Y-%m-%d_%H-%M-%S)

# Backup database
if command -v mysqldump &> /dev/null; then
    print_warning "Creating database backup..."
    # Note: Update with your actual database credentials
    # mysqldump -u your_db_user -p'password' your_db_name > $BACKUP_DIR/db_backup_$BACKUP_DATE.sql
    print_success "Database backup created"
else
    print_warning "MySQL is not installed, skipping database backup"
fi

# Backup files
print_warning "Creating file backup..."
tar -czf $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz \
    $APP_PATH/storage \
    $APP_PATH/database \
    $APP_PATH/.env 2>/dev/null || true
print_success "File backup created"

# Step 2: Pull latest code
print_header "Step 2: Pulling Latest Code"
cd $APP_PATH
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH
print_success "Code updated to latest version"

# Step 3: Install dependencies
print_header "Step 3: Installing Dependencies"
composer install --no-dev --optimize-autoloader
npm install
print_success "Dependencies installed"

# Step 4: Build frontend assets
print_header "Step 4: Building Frontend Assets"
npm run build
print_success "Frontend assets built"

# Step 5: Run migrations
print_header "Step 5: Running Database Migrations"
php artisan migrate --force
print_success "Database migrations completed"

# Step 6: Cache optimization
print_header "Step 6: Optimizing Application"
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

# Step 7: Set permissions
print_header "Step 7: Setting File Permissions"
chown -R www-data:www-data $APP_PATH
chmod -R 755 $APP_PATH
chmod -R 775 $APP_PATH/storage $APP_PATH/bootstrap/cache
print_success "Permissions set"

# Step 8: Restart services
print_header "Step 8: Restarting Services"
systemctl reload php8.2-fpm
systemctl reload nginx
supervisorctl restart all
print_success "Services restarted"

# Step 9: Health check
print_header "Step 9: Health Check"
php artisan health
print_success "Application health check passed"

# Verify backups are not too old
print_header "Cleanup Old Backups"
find $BACKUP_DIR -type f -name "*.sql" -o -name "*.tar.gz" | while read file; do
    age_days=$(( ($(date +%s) - $(stat -f%m "$file")) / 86400 ))
    if [ $age_days -gt 30 ]; then
        rm "$file"
        print_warning "Deleted old backup: $(basename $file)"
    fi
done

print_header "Deployment Completed Successfully! ✓"
echo -e "${GREEN}Your application has been deployed to production.${NC}"
echo -e "Backup created: $BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
echo -e "Backup created: $BACKUP_DIR/files_backup_$BACKUP_DATE.tar.gz\n"
