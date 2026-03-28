# Elgon Tech - Deployment Guide

## Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ (for frontend assets)
- SSL Certificate (recommended)

## Deployment Steps

### 1. Server Preparation
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-gd php8.2-curl
sudo apt install -y mysql-server
sudo apt install -y nodejs npm
sudo apt install -y git curl supervisor nginx
```

### 2. Clone Repository
```bash
cd /var/www
git clone your-repository-url elgon-tech
cd elgon-tech
```

### 3. Install Dependencies
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm install

# Build frontend assets
npm run build
```

### 4. Configure Environment
```bash
# Copy production environment file
cp .env.production.example .env

# Generate application key
php artisan key:generate

# Update database credentials in .env file
nano .env

# Run database migrations
php artisan migrate --force

# Seed database with initial data (if needed)
php artisan db:seed
```

### 5. File Permissions
```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/elgon-tech
sudo chmod -R 755 /var/www/elgon-tech
sudo chmod -R 775 /var/www/elgon-tech/storage
sudo chmod -R 775 /var/www/elgon-tech/bootstrap/cache
```

### 6. Configure Web Server (Nginx)
```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/elgon-tech
```

Add the following configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    # SSL certificates (replace with your paths)
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    root /var/www/elgon-tech/public;
    index index.php index.html index.htm;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript
               application/x-javascript application/xml+rss
               application/javascript application/json;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/elgon-tech /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. Configure PHP-FPM
```bash
# Edit PHP-FPM configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Set user to www-data and adjust settings as needed
sudo systemctl restart php8.2-fpm
```

### 8. Setup SSL with Let's Encrypt
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Generate SSL certificate
sudo certbot certonly --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renew certificate
sudo systemctl enable certbot.timer
```

### 9. Setup Queue Worker (Supervisor)
```bash
# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/elgon-tech-worker.conf
```

Add the following:
```ini
[program:elgon-tech-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/elgon-tech/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/elgon-tech-worker.log
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### 10. Setup Cron Jobs
```bash
# Edit crontab
sudo crontab -e
```

Add the following:
```
* * * * * cd /var/www/elgon-tech && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Verify Installation
```bash
# Check application health
php artisan health

# Check logs
tail -f storage/logs/laravel.log
```

### 12. Performance Optimization
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## Backup Strategy

### Daily Backup Script
```bash
#!/bin/bash
# Create backup script at /usr/local/bin/backup-elgon-tech.sh

BACKUP_DIR="/var/backups/elgon-tech"
DATE=$(date +%Y-%m-%d_%H-%M-%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u your_db_user -p'your_db_password' elgon_tech_db > $BACKUP_DIR/db_backup_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/elgon-tech/storage /var/www/elgon-tech/database

# Delete backups older than 30 days
find $BACKUP_DIR -name "*.sql" -o -name "*.tar.gz" | xargs find ... -mtime +30 -delete
```

Schedule with cron:
```
0 2 * * * /usr/local/bin/backup-elgon-tech.sh >> /var/log/backup-elgon-tech.log 2>&1
```

## Monitoring & Maintenance

### Monitor Application
- Use tools like Sentry or Bugsnag for error tracking
- Setup log monitoring with tools like ELK Stack
- Monitor server resources with tools like Prometheus + Grafana

### Regular Updates
```bash
# Keep dependencies updated
composer update --no-dev

# Update Laravel framework
composer require laravel/framework:latest

# Run migrations
php artisan migrate
```

## Troubleshooting

### 500 Error
```bash
tail -f storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
```

### Database Connection Error
```bash
# Verify database credentials in .env
php artisan tinker
# Test: \Illuminate\Support\Facades\DB::connection()->getPdo()
```

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/elgon-tech
sudo chmod -R 755 /var/www/elgon-tech
sudo chmod -R 775 /var/www/elgon-tech/storage
```

## Support
For additional help, refer to the official Laravel documentation: https://laravel.com/docs
