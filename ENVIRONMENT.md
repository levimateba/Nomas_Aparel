# Environment Setup Guide

## Development Environment

### Prerequisites
- PHP 8.2+
- Composer
- SQLite (included with PHP) or MySQL
- Node.js 18+
- Git

### Setup Instructions

```bash
# 1. Clone the repository
git clone your-repository-url
cd elgon-tech

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Setup environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Run migrations
php artisan migrate

# 7. Build frontend assets
npm run dev

# 8. Start development server
php artisan serve

# The application will be available at http://localhost:8000
```

## Production Environment

### Prerequisites
- Ubuntu 20.04 LTS or later
- SSH access to server
- Domain name with DNS pointing to server
- Email account for mail configuration

### Quick Setup

1. **Follow the DEPLOYMENT.md guide** which includes:
   - Server preparation
   - PHP and database installation
   - Code deployment
   - Nginx configuration
   - SSL setup with Let's Encrypt
   - Queue workers configuration
   - Backup strategy

### Key Production Settings

**Database:**
- Use MySQL 8.0 or later
- Create dedicated database user with limited permissions
- Enable SSL for database connections

**Mail:**
- Configure SMTP using services like:
  - Gmail
  - Mailgun
  - SendGrid
  - AWS SES
  - Mailtrap (testing only)

**Storage:**
- For small deployments: Use local storage
- For scalability: Configure AWS S3 or similar
- Ensure storage directory has proper permissions

**Security:**
- Enable HTTPS only (redirect HTTP to HTTPS)
- Set APP_DEBUG=false
- Use strong database passwords
- Enable database backups
- Regular security updates

### Environment Variables

**Critical Variables (must be changed from defaults):**
```
APP_KEY=         # Generate with: php artisan key:generate
APP_ENV=         # Set to 'production'
APP_DEBUG=       # Set to 'false'
APP_URL=         # Your domain URL
DB_PASSWORD=     # Strong database password
MAIL_*=          # Your email provider credentials
```

**Performance Variables:**
```
LOG_LEVEL=warning
CACHE_STORE=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=database
FILESYSTEM_DISK=s3  # If using S3
```

## Local Development Recommendations

### Code Style
Follow PSR-12 coding standards. Use Laravel Pint for formatting:
```bash
./vendor/bin/pint
```

### Testing
Run tests before deployment:
```bash
php artisan test
php artisan test --parallel
```

### Database
Use SQLite for development to keep things simple:
```bash
# In .env
DB_CONNECTION=sqlite
```

### Debugging
- Use Laravel Debugbar for development
- Use Tinker for testing code snippets:
```bash
php artisan tinker
```

## Docker Setup (Alternative)

### Using Docker

```bash
# 1. Build Docker image
docker build -t elgon-tech .

# 2. Run container
docker run -p 8000:8000 \
    -e DB_HOST=mysql \
    -e DB_DATABASE=elgon_tech \
    -e DB_USERNAME=root \
    -e DB_PASSWORD=secret \
    elgon-tech

# 3. Run migrations
docker exec <container-id> php artisan migrate
```

### Using Docker Compose

```bash
# 1. Build containers
docker-compose build

# 2. Start services
docker-compose up -d

# 3. Run migrations
docker-compose exec app php artisan migrate

# 4. Access application at http://localhost
```

## Environment Troubleshooting

### PHP Configuration
Check your PHP configuration:
```bash
php -i                              # Show all PHP info
php -m                              # List loaded modules
php --ini                           # Show .ini file locations
```

### Database Connection
Test database connection:
```bash
php artisan tinker
# In tinker:
DB::connection()->getPdo()
DB::select('SELECT 1')
```

### permissions issues
```bash
# Fix permission errors
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data .
```

### Logs
Monitor application logs:
```bash
tail -f storage/logs/laravel.log
```

## Next Steps

1. **Configure your mail provider** - Update MAIL_* variables in .env
2. **Setup database backups** - Follow backup strategy in DEPLOYMENT.md
3. **Configure monitoring** - Setup error tracking and logging
4. **Custom domain** - Point your domain to the server
5. **SSL certificate** - Set up HTTPS with Let's Encrypt
6. **Email verification** - Configure email verification for user registration

For detailed deployment instructions, see **DEPLOYMENT.md**
