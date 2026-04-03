# Production Deployment Checklist

This project is Laravel-based and uses queue/database/SMS integrations.

## 1) Server prerequisites

- PHP 8.2+ with required extensions (`mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`)
- Composer
- MariaDB/MySQL
- Nginx or Apache
- Process manager for queues (`supervisor` recommended)

## 2) Upload and install

```bash
composer install --no-dev --optimize-autoloader
cp .env.production.example .env
php artisan key:generate
```

Fill `.env` with real production values (DB, mail, SMS, domain).

## 3) Database and app bootstrap

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

## 4) Cache for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5) Queue worker (required)

This project uses `QUEUE_CONNECTION=database`.

Run worker:

```bash
php artisan queue:work --tries=3 --timeout=120
```

Recommended: run with Supervisor and auto-restart on failure/reboot.

## 6) SMS (Semaphore) requirements

Set in `.env`:

- `SMS_ENABLED=true`
- `SMS_API_KEY=<your_api_key>`
- `SMS_SENDER_NAME=<approved_sender_name>`
- `SMS_BASE_URL=https://api.semaphore.co/api/v4`

Important:

- Sender name must be approved by Semaphore.
- Account must be approved/top-upped, otherwise provider returns `HTTP 403`.

## 7) Post-deploy smoke test

Run these after each release (or hotfix) before announcing go-live. Adjust steps if a module is disabled in `.env`.

### Core

1. **HTTPS** — Site loads over `https://`; no browser certificate warnings for your domain.
2. **Public site** — Home / login page loads without 500 errors (`storage/logs/laravel.log` stays clean on first hit).
3. **Admin login** — Sign in as an admin; session persists across one navigation (dashboard → inner page → back).
4. **Dashboard** — `/admin/dashboard` (or your default admin home) renders without errors.

### Data & reports

5. **Residents list** — Open the residents index; pagination or search if you use it regularly.
6. **Reports** — Open **Reports →** one of Population / Households / Blotter; trigger **Export Excel** (or PDF) once and confirm download starts (no 500).  
   - Note: super admin accounts are excluded from the residents list by design; use an admin/staff account for list checks.

### Registration & queues (if used)

7. **Registration** — If public registration is enabled, open the register form and submit a test applicant (or confirm validation errors display correctly).
8. **Queue worker** — If you use jobs (mail, SMS, etc.), confirm **Supervisor** (or your worker) is running: `php artisan queue:work` equivalent, and failed jobs table is empty or expected.

### SMS (Semaphore)

When `SMS_ENABLED=true`:

9. Open `/admin/sms`.
10. Send **Test SMS** to a number you control.
11. Confirm the latest `sms_logs` row has:
    - `status = sent`
    - non-empty `sent_at`

### Email (if SMTP configured)

12. Trigger a flow that sends mail (e.g. password reset) and confirm delivery or a clean entry in `storage/logs/laravel.log`.

## 8) Security reminders

- `APP_ENV=production`
- `APP_DEBUG=false`
- Never commit `.env`
- Restrict access to server/user permissions
- Enable HTTPS on domain

### Automated tests (CI)

If the repo is on **GitHub**, pushes and pull requests to `main` / `master` / `develop` run **PHPUnit** via `.github/workflows/tests.yml` (in-memory SQLite from `phpunit.xml`, not your production database).

Locally before deploy:

```bash
composer test
# or: php artisan test
```

### Repository hygiene (do not commit)

- **Never commit** `.env` or production secrets (use `.env.example` / `.env.production.example` as templates only).
- **Database dumps** — Large SQL backups (e.g. `backup*.sql` in the project root) should stay out of git; store them on the server or secure backup storage. If a dump was committed by mistake, remove it from tracking: `git rm --cached <file>` then add the pattern to `.gitignore`.
- **Cache** — `.phpunit.result.cache` is ignored; do not commit PHPUnit cache files.

---

## 9) DigitalOcean (Droplet) + Nginx

Example site config (adjust `server_name`, `root`, PHP-FPM socket):

- **File:** `deploy/nginx-barangay-paguiruan.conf.example`

```bash
sudo cp deploy/nginx-barangay-paguiruan.conf.example /etc/nginx/sites-available/barangay-paguiruan
sudo ln -sf /etc/nginx/sites-available/barangay-paguiruan /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

**SSL (Let's Encrypt):**

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

**PHP-FPM:** ensure version matches socket in the config (e.g. `php8.2-fpm`).

**Permissions (typical):**

```bash
sudo chown -R www-data:www-data /var/www/barangay-paguiruan/storage /var/www/barangay-paguiruan/bootstrap/cache
```

---

## 10) Supervisor — queue worker

- **File:** `deploy/supervisor-laravel-worker.conf.example`

```bash
sudo cp deploy/supervisor-laravel-worker.conf.example /etc/supervisor/conf.d/laravel-worker.conf
# Edit paths/user if needed
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 11) Cron — Laravel scheduler

- **File:** `deploy/cron-laravel.example`

```bash
sudo crontab -e -u www-data
# Add the line from cron-laravel.example (adjust project path)
```

---

## 12) Email — Hostinger SMTP

Use the mailbox you create in **Hostinger hPanel** (same domain as production is best).

Typical `.env` (also in `.env.production.example`):

| Variable | Typical value |
|----------|----------------|
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | `smtp.hostinger.com` |
| `MAIL_PORT` | `465` |
| `MAIL_SCHEME` | `smtps` |
| `MAIL_USERNAME` | full email, e.g. `noreply@your-domain.com` |
| `MAIL_PASSWORD` | mailbox password |
| `MAIL_FROM_ADDRESS` | same or another verified mailbox |
| `MAIL_FROM_NAME` | display name |

**DNS:** add Hostinger’s **SPF** and **DKIM** records for your domain (hPanel → DNS / Email) so deliverability is good.

**Test:** after deploy, trigger a password reset or registration email and check logs: `storage/logs/laravel.log`.
