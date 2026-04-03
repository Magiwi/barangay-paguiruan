# Barangay Paguiruan — e-Governance

Laravel-based barangay information and services system (residents, households, certificates, permits, reports, SMS, and related modules).

## Requirements

- PHP 8.2+
- Composer
- MySQL/MariaDB (production)
- Node/npm (optional — for front-end asset builds if you change Vite sources)

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Tests

Automated tests use SQLite in memory (`phpunit.xml`). They do not use your `.env` database.

```bash
composer test
# or: php artisan test
```

On **GitHub**, `.github/workflows/tests.yml` runs the same suite on push/PR to `main`, `master`, or `develop`.

## Deployment

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for production server, Nginx, Supervisor, queues, SMS (Semaphore), and email.

End-user oriented notes: **[USER_MANUAL.md](USER_MANUAL.md)**.

## License

This application builds on [Laravel](https://laravel.com) (MIT). Project-specific licensing is subject to your organization’s policy.
