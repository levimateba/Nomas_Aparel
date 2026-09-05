# Nomas Apparel — Truhost Deployment (nomas.elgontech.co.ke)

Deploy this Laravel app on **Truhost cPanel** at `https://nomas.elgontech.co.ke`.

## Requirements

- Truhost cPanel login for `elgontect.co.ke`
- PHP **8.2+** (set in cPanel → **MultiPHP Manager**)
- MySQL database
- cPanel **Terminal** or SSH access
- Git (optional — cPanel **Git™ Version Control**)

---

## Step 1 — Create the subdomain

1. Log in to **Truhost cPanel**
2. Go to **Domains → Subdomains** (or **Create A New Domain**)
3. Create:
   - **Subdomain:** `nomas`
   - **Domain:** `elgontect.co.ke`
   - **Document Root:** `/home/YOUR_CPANEL_USERNAME/nomas-apparel/public`  
     *(Replace `YOUR_CPANEL_USERNAME` with your cPanel username)*

> **Important:** Point the document root at the **`public`** folder, not the project root. This keeps `.env` and app code off the web.

---

## Step 2 — Create MySQL database

1. cPanel → **MySQL® Databases**
2. Create database: `YOUR_CPANEL_USERNAME_nomas`
3. Create user with a strong password
4. Add user to database with **ALL PRIVILEGES**
5. Note host (usually `localhost`), database name, username, and password

---

## Step 3 — Upload the application

### Option A — Git (recommended)

1. cPanel → **Git™ Version Control** → **Create**
2. Clone URL: `https://github.com/levimateba/Nomas_Aparel.git`
3. Repository Path: `/home/YOUR_CPANEL_USERNAME/nomas-apparel`
4. Click **Create**, then **Pull or Deploy** → branch `main`

### Option B — Upload ZIP

On your Mac, from the project folder:

```bash
chmod +x scripts/package-for-truhost.sh
./scripts/package-for-truhost.sh
```

Upload `nomas-apparel-deploy.zip` via cPanel **File Manager** to `/home/YOUR_CPANEL_USERNAME/` and extract as `nomas-apparel`.

---

## Step 4 — Configure environment

In cPanel **Terminal**:

```bash
cd ~/nomas-apparel
cp .env.production.example .env
nano .env   # or edit via File Manager
```

Update these values:

| Variable | Value |
|----------|-------|
| `APP_URL` | `https://nomas.elgontect.co.ke` |
| `DB_DATABASE` | your cPanel database name |
| `DB_USERNAME` | your cPanel database user |
| `DB_PASSWORD` | your database password |
| `MAIL_*` | your Truhost email settings |

Generate the app key:

```bash
php artisan key:generate
```

---

## Step 5 — Install dependencies & migrate

```bash
cd ~/nomas-apparel

# If Composer is available on Truhost:
composer install --no-dev --optimize-autoloader

# If Composer is NOT on the server, upload vendor/ from your local package build.

php artisan migrate --force
php artisan db:seed --force

php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 775 storage bootstrap/cache
```

Or run the helper script:

```bash
chmod +x scripts/truhost-post-deploy.sh
./scripts/truhost-post-deploy.sh
```

---

## Step 6 — SSL (HTTPS)

1. cPanel → **SSL/TLS Status** or **Let's Encrypt™ SSL**
2. Issue certificate for `nomas.elgontect.co.ke`
3. Enable **Force HTTPS Redirect** if available

Ensure `.env` has:

```
APP_URL=https://nomas.elgontect.co.ke
SESSION_SECURE_COOKIE=true
```

---

## Step 7 — Cron job (required for Laravel scheduler)

cPanel → **Cron Jobs** → add:

```
* * * * * cd /home/YOUR_CPANEL_USERNAME/nomas-apparel && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Use the PHP path shown in cPanel **MultiPHP Manager** if `/usr/local/bin/php` differs.

---

## Step 8 — Verify

1. Visit `https://nomas.elgontect.co.ke` — storefront should load
2. Visit `https://nomas.elgontect.co.ke/up` — should return `{"status":"ok"}`
3. Admin: `https://nomas.elgontect.co.ke/admin`  
   Default seed login: `admin@example.com` / `password123` — **change immediately after first login**

---

## Updating after changes

On the server:

```bash
cd ~/nomas-apparel
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build   # or upload pre-built public/build from local package
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Or use `./deploy.sh` if configured on a VPS with root access.

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| **500 error** | Check `storage/logs/laravel.log`; ensure `storage` and `bootstrap/cache` are writable (`chmod -R 775`) |
| **404 on all routes** | Document root must be `.../nomas-apparel/public`, not project root |
| **Mixed content / session issues** | Set `APP_URL` to `https://...` and `SESSION_SECURE_COOKIE=true` |
| **Database connection failed** | Use `localhost` as `DB_HOST`; verify cPanel DB name/user prefix |
| **CSS/JS missing** | Run `npm run build` locally and upload `public/build/` |
| **Composer not found** | Build locally with `./scripts/package-for-truhost.sh` and upload `vendor/` |

---

## DNS (if subdomain is new)

If `nomas.elgontect.co.ke` does not resolve yet, add in your domain DNS (at Truhost or registrar):

| Type | Name | Value |
|------|------|-------|
| A | `nomas` | Truhost server IP (from cPanel → **Server Information**) |
| or CNAME | `nomas` | `elgontect.co.ke` |

Propagation can take up to 24 hours.
