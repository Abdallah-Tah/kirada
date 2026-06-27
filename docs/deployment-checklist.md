# Kirada Deployment Checklist

> Last updated: 2026-06-27 (Phase 17)

## 1. Server Requirements

- **OS**: Ubuntu 22.04+ or Debian 12+
- **PHP**: 8.4+ (verified with 8.4.22)
- **Web server**: Nginx + PHP-FPM (recommended) or Apache with mod_rewrite
- **Database**: MySQL 8.0+ or MariaDB 10.11+
- **Node.js**: 20+ (for frontend asset builds, not needed at runtime)
- **Composer**: 2.7+
- **Redis** (optional, recommended): for cache/queue in multi-server setups

## 2. PHP Extensions

Required:
- `php8.4-fpm` (or `php8.4-cli`)
- `php-mysql` (MySQL/MariaDB driver)
- `php-xml`
- `php-mbstring`
- `php-zip`
- `php-gd`
- `php-curl`
- `php-intl`
- `php-bcmath`
- `php-json`
- `php-tokenizer`
- `php-fileinfo`

Optional:
- `php-redis` (if using Redis for cache/queue)
- `php-memcached` (if using Memcached)

Verify:
```bash
php -m | grep -E "mysql|xml|mbstring|zip|gd|curl|intl|bcmath"
```

## 3. MySQL/MariaDB Setup

```sql
CREATE DATABASE kirada CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kirada'@'localhost' IDENTIFIED BY '<strong-password>';
GRANT ALL PRIVILEGES ON kirada.* TO 'kirada'@'localhost';
FLUSH PRIVILEGES;
```

## 4. Application Setup

```bash
# Clone
git clone <repo-url> /var/www/kirada
cd /var/www/kirada

# Dependencies (no dev in production)
composer install --no-dev --optimize-autoloader

# Environment
cp .env.example .env
php artisan key:generate

# Edit .env:
# - APP_NAME=Kirada
# - APP_ENV=production
# - APP_DEBUG=false
# - APP_URL=https://your-domain.com
# - DB_PASSWORD=<strong-password>
# - SESSION_DOMAIN=.your-domain.com
# - SESSION_SECURE_COOKIE=true
# - MAIL_* (real SMTP credentials)
# - OPENAI_API_KEY= (optional)
# - KIRADA_ADMIN_EMAIL= (real admin email)
# - KIRADA_ADMIN_PASSWORD= (strong initial password)

# Migrate + seed (production mode)
php artisan migrate --force
php artisan db:seed --force --class=RolePermissionSeeder
php artisan db:seed --force --class=CountryCurrencySeeder
php artisan db:seed --force --class=PlanSeeder
php artisan db:seed --force --class=AdminUserSeeder
# AdminUserSeeder in production creates ONLY the admin user

# Build frontend assets
npm install
npm run build

# Storage link
php artisan storage:link

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 5. Storage + Private Disk

- **Public disk** (`storage/app/public`): symlinked to `public/storage/` via `php artisan storage:link`
- **Private disk** (`storage/app/private`): NOT web-accessible. Used for:
  - Payment proofs (`rent-payments/`)
  - Documents (`documents/`)
  - Downloads go through `DocumentController` with policy authorization

Verify:
```bash
ls -la public/storage  # should be symlink to ../storage/app/public
ls -la storage/app/private  # should exist and NOT be web-accessible
```

For multi-server or S3:
- Update `FILESYSTEM_DISK=s3` in `.env`
- Configure AWS credentials
- Update the `private` disk in `config/filesystems.php` to use S3

## 6. APP_KEY

- Must be set before first deployment: `php artisan key:generate`
- Never commit `.env` to version control
- If rotating the key, all existing sessions are invalidated

## 7. HTTPS / TLS

- Use Let's Encrypt (certbot) or a managed TLS certificate
- Force HTTPS in Nginx:
```nginx
server {
    listen 443 ssl http2;
    server_name kirada.example.com;

    ssl_certificate /etc/letsencrypt/live/kirada.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/kirada.example.com/privkey.pem;

    root /var/www/kirada/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to private storage
    location ~* /storage/app/private/ {
        deny all;
        return 404;
    }
}

# HTTP → HTTPS redirect
server {
    listen 80;
    server_name kirada.example.com;
    return 301 https://$server_name$request_uri;
}
```

## 8. Queue Worker

Kirada uses the `database` queue connection by default. For production:

```bash
# Install Supervisor
sudo apt install supervisor

# Create worker config
sudo tee /etc/supervisor/conf.d/kirada-worker.conf <<EOF
[program:kirada-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/kirada/artisan queue:work --max-time=3600 --max-jobs=100
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/kirada/storage/logs/worker.log
stopwaitsecs=3600
EOF

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start kirada-worker:*
```

For Redis queue (recommended for higher traffic):
- Set `QUEUE_CONNECTION=redis` in `.env`
- Configure Redis connection in `.env`

## 9. Scheduler Cron

```bash
# Add to www-data crontab or /etc/cron.d/kirada
* * * * * cd /var/www/kirada && php artisan schedule:run >> /dev/null 2>&1
```

## 10. Mail Configuration

Set in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Kirada"
```

Test mail:
```bash
php artisan tinker
>>> Mail::raw('Test from Kirada', fn($m) => $m->to('you@example.com')->subject('Test'));
```

## 11. OpenAI API Key (Optional)

- Leave `OPENAI_API_KEY` empty to disable the AI Assistant (shows friendly "not configured" message)
- Set a valid key to enable the chatbot
- Model defaults to `gpt-4o-mini` (cost-effective)
- AI is read-only in Phase 16 — no write actions possible

## 12. PWA Verification

After deployment, verify:
- `https://your-domain.com/manifest.json` returns valid JSON
- `https://your-domain.com/service-worker.js` returns JS with correct content-type
- `https://your-domain.com/offline` returns 200
- Install prompt appears on mobile Chrome/Safari
- Icons load: `/icons/icon-192.png`, `/icons/icon-512.png`
- Service worker registration shows in browser dev tools

## 13. First Admin Creation

In production, `AdminUserSeeder` creates ONLY the admin user (not test accounts).

```bash
# Set admin credentials in .env before seeding
KIRADA_ADMIN_EMAIL=real-admin@example.com
KIRADA_ADMIN_PASSWORD=<strong-temporary-password>

# Seed
php artisan db:seed --force --class=AdminUserSeeder

# ⚠️ Change the password immediately after first login!
```

## 14. Production Seeding Rules

**Always seed (required):**
```bash
php artisan db:seed --force --class=RolePermissionSeeder   # Roles + permissions
php artisan db:seed --force --class=CountryCurrencySeeder   # Countries + currencies
php artisan db:seed --force --class=PlanSeeder              # Subscription plans
```

**Production-only:**
```bash
php artisan db:seed --force --class=AdminUserSeeder         # Creates ONLY admin in production
```

**NEVER in production (dev/test only):**
- `AdminUserSeeder` in non-production env creates 4 test accounts with `password` as password
- Any future factory-based seeders

**Order matters:** RolePermission → CountryCurrency → Plan → AdminUser

## 15. Backup Plan

### Database (daily, automated)
```bash
# Add to crontab or backup script:
mysqldump -u kirada -p<password> kirada | gzip > /backups/kirada-db-$(date +%Y%m%d).sql.gz

# Retain 30 days:
find /backups/ -name "kirada-db-*.sql.gz" -mtime +30 -delete
```

### Documents + payment proofs (daily)
```bash
# The private disk at storage/app/private contains sensitive files
rsync -a /var/www/kirada/storage/app/private/ /backups/private/
```

### Full application backup (weekly)
```bash
tar -czf /backups/kirada-app-$(date +%Y%m%d).tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/app/private' \
  /var/www/kirada/
```

### Restoration test (quarterly)
- Restore DB dump to a test server
- Verify application boots
- Verify document downloads work
- Document recovery time

## 16. Post-Deployment Verification

```bash
# Run all checks
php artisan test                    # Should pass (38 tests, 10 skipped)
php artisan route:list              # Should show 90 routes
php artisan migrate:status          # All migrations should be "Ran"
php artisan config:cache            # Cache config
php artisan route:cache             # Cache routes
php artisan view:cache              # Cache views
npm run build                       # Build assets
```

Verify in browser:
- [ ] Login page loads at `/login`
- [ ] Admin can log in and see dashboard with metrics
- [ ] Landlord can log in and see dashboard with metrics
- [ ] Tenant can log in and see dashboard
- [ ] Maintenance can log in and see dashboard
- [ ] Properties/Units/Tenants/Leases CRUD works
- [ ] Invoice creation from lease works
- [ ] Payment recording + confirmation works
- [ ] Maintenance request creation works
- [ ] Messaging between landlord + tenant works
- [ ] Document upload + download works
- [ ] AI Assistant page loads (shows "not configured" if no key)
- [ ] PWA installable on mobile
- [ ] Offline page loads when network disabled

## 17. Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_KEY` set and not committed to git
- [ ] Database user has minimal privileges (only kirada DB)
- [ ] `.env` file not web-accessible (Nginx should block dotfiles)
- [ ] `storage/app/private/` not web-accessible
- [ ] HTTPS enforced (HTTP redirects to HTTPS)
- [ ] Session cookies are `Secure` + `SameSite=Lax`
- [ ] Strong admin password set
- [ ] OpenAI API key (if used) has spending limits set
- [ ] Firewall: only 80/443 + SSH open
- [ ] Fail2ban or similar for SSH brute-force protection
- [ ] Regular security updates (unattended-upgrades)