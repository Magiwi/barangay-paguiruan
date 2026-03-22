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

1. Login as admin.
2. Open `/admin/sms`.
3. Send **Test SMS**.
4. Confirm latest `sms_logs` entry has:
   - `status = sent`
   - non-empty `sent_at`

## 8) Security reminders

- `APP_ENV=production`
- `APP_DEBUG=false`
- Never commit `.env`
- Restrict access to server/user permissions
- Enable HTTPS on domain

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
