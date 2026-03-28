# Quick Start Guide - New Features

## 🚀 Email System

### Setup Email (5 minutes)
1. Get SMTP credentials from a provider:
   - **Gmail**: Enable "App Password" in security settings
   - **Mailtrap**: Free account at mailtrap.io
   - **SendGrid**: Create API key
   - **Mailgun**: Create account

2. Update `.env` file:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com           # Your provider's SMTP host
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password    # Not your Gmail password!
   MAIL_FROM_ADDRESS=noreply@elgontech.com
   MAIL_FROM_NAME=Elgon Tech
   ```

3. Test email sending:
   ```bash
   php artisan mail:test your-email@gmail.com
   ```

4. Test contact form:
   - Go to website
   - Fill contact form
   - Check your email for the submission

✨ **Features**: 
- Admin receives detailed contact information
- Visitors get confirmation email
- Emails are beautifully formatted with HTML

---

## 👤 User Registration

### Features
- Public user registration system
- Secure password hashing (BCRYPT)
- Email validation
- Session management
- Login/Logout functionality

### How to Use:

**Register a new account:**
1. Click "Register" button in navbar
2. Enter: Name, Email, Password (8+ chars)
3. Click "Create Account"
4. You're automatically logged in!

**Login to existing account:**
1. Click "Login" button in navbar
2. Enter Email and Password
3. Click "Login"
4. You'll see your name in navbar

**Logout:**
1. Click "Logout" button (visible when logged in)
2. You're logged out

### Admin vs Users
- **Admin Users**: Access `/admin` panel to manage site content
- **Regular Users**: Can register on website, view content
- Admins created via database (is_admin = 1)

### Database Reference:
```sql
-- Check registered users
SELECT name, email, is_admin, created_at FROM users;

-- Make a user an admin
UPDATE users SET is_admin = 1 WHERE email = 'user@example.com';
```

---

## 🌐 Production Deployment

### Quick Deployment (30 minutes)

**Option 1: Automated (Recommended)**
```bash
chmod +x deploy.sh
./deploy.sh
```

**Option 2: Manual (Follow DEPLOYMENT.md)**
```bash
# 1. Prepare server
sudo apt update
sudo apt install -y php8.2-cli composer nodejs

# 2. Clone and install
git clone your-repo-url
cd elgon-tech
composer install --no-dev
npm run build

# 3. Configure
cp .env.production.example .env
php artisan key:generate
php artisan migrate --force

# 4. Setup web server
# Copy nginx/elgon-tech.conf to /etc/nginx/sites-available/
# Following DEPLOYMENT.md guide
```

### Required for Production:
- [ ] Domain name pointing to server
- [ ] SSL certificate (Let's Encrypt - free)
- [ ] Database (MySQL 8.0+)
- [ ] Email service configured
- [ ] Backups configured

### Check Deployment Status:
```bash
php artisan health
tail -f storage/logs/laravel.log
```

---

## 📋 Configuration Checklist

### Before Going Live:
- [ ] Configure email service in `.env`
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false in `.env`
- [ ] Setup SSL certificate
- [ ] Configure database backups
- [ ] Test user registration
- [ ] Test contact form emails
- [ ] Review SECURITY.md checklist

### After Deployment:
- [ ] Monitor application logs
- [ ] Test all features in production
- [ ] Setup error tracking (Sentry, Bugsnag)
- [ ] Configure database backups
- [ ] Setup monitoring alerts

---

## 🔧 Troubleshooting

### Email Not Sending
```bash
# Check mail configuration
php artisan config:show mail

# Test SMTP connection
php artisan tinker
Mail::to('test@example.com')->send(new \App\Mail\ContactMail('Test', 'test@example.com', 'Test', 'Test'));
```

### User Registration Issues
```bash
# Check database
php artisan tinker
User::all()  # See all users

# Check auth middleware
php artisan route:list | grep auth
```

### Deployment Problems
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check Nginx errors (if on server)
tail -f /var/log/nginx/error.log

# Check PHP-FPM status
sudo systemctl status php8.2-fpm
```

---

## 📚 Documentation Files

- **DEPLOYMENT.md** - Complete deployment guide (40+ pages)
- **ENVIRONMENT.md** - Environment setup guide
- **SECURITY.md** - Security checklist before production
- **IMPLEMENTATION_SUMMARY.md** - Technical details of new features

---

## 💡 Example Commands

```bash
# Development
php artisan serve                    # Start dev server
npm run dev                          # Watch CSS/JS
php artisan tinker                  # Test code

# Email testing
php artisan mail:test your@email.com

# Database
php artisan migrate                 # Run migrations
php artisan db:seed                # Seed sample data
php artisan tinker                 # Database tests

# Cache/Optimization
php artisan config:cache           # Optimize config
php artisan route:cache            # Cache routes
php artisan view:cache             # Cache views

# Production
php artisan health                 # Check app health
php artisan queue:work             # Start queue worker
php artisan schedule:run           # Run scheduled tasks
```

---

## 🎨 Customize Colors

All colors are CSS variables in `resources/views/layouts/frontend.blade.php`:
```css
:root {
    --primary-dark: #16456e;    /* Dark blue */
    --primary-green: #165752;   /* Green */
    --primary-brown: #573c16;   /* Brown */
}
```

Edit the hex values to change site colors.

---

## 📞 Support

For issues:
1. Check the relevant documentation (.md file)
2. Review application logs: `storage/logs/laravel.log`
3. Check [Laravel Documentation](https://laravel.com/docs)
4. Run `php artisan health` to verify setup

---

## ✨ Next Features to Consider

After getting comfortable with these features:
- Email verification for new registrations
- Password reset functionality
- Two-factor authentication (2FA)
- Social login (Google, GitHub, etc.)
- API authentication (Laravel Sanctum)
- Admin role-based access control
- Email notification preferences
- User profile pages

Happy deploying! 🚀
