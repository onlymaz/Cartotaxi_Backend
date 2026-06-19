# CargoTaxi Backend — Production Deployment Checklist

Target: **https://cargotaxi.at** (Laravel API + customer/admin web panel).
Both iOS apps point at this domain in their Release builds, so the platform is
non-functional until this is live.

---

## 0. Server prerequisites
- [ ] **PHP 8.3+** with extensions: `pdo_mysql`, `mbstring`, `bcmath`, `openssl`, `gd`, `curl`, `zip`, `fileinfo`, `intl`
- [ ] **Composer 2**
- [ ] **MySQL 8 / MariaDB 10.4+** (or PostgreSQL) — create a database + user
- [ ] **Supervisor** (for the queue worker) and **cron** (for the scheduler, if used)
- [ ] A web server (Nginx/Apache) with the docroot pointing at **`public/`**
- [ ] Valid **HTTPS certificate** for `cargotaxi.at` (Let's Encrypt is fine)

## 1. Code + dependencies
- [ ] Pull the code to the server (git or upload).
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `composer dump-autoload -o`

## 2. Environment
- [ ] `cp .env.production.example .env`
- [ ] Fill **every** `<...>` placeholder in `.env`:
  - DB creds, `MAIL_*` (real SMTP, **not** mailtrap), `JWT_SECRET` (`openssl rand -base64 48`)
  - `GOOGLE_CLIENT_SECRET`, `STRIPE_KEY`/`STRIPE_SECRET` (**live**), `FIREBASE_*`
- [ ] Confirm: `APP_ENV=production`, `APP_DEBUG=false`, `TELESCOPE_ENABLED=false`, `FRONTEND_URL=https://cargotaxi.at`
- [ ] `php artisan key:generate`

## 3. Database
- [ ] `php artisan migrate --force`
- [ ] Seed only what production needs (gateways, packages, roles, site settings, an admin user). **Do NOT** run the demo/test seeders (OrderSeeder, LiveTrackingTestSeeder, etc.).

## 4. Secrets / files
- [ ] Upload `storage/app/firebase/service-account.json` (keep it **outside** `public/`).
- [ ] `php artisan storage:link`
- [ ] Permissions: `storage/` and `bootstrap/cache/` writable by the web user.

## 5. Caches (after .env is final)
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan event:cache`

## 6. Queue worker (REQUIRED — emails & notifications don't send without it)
- [ ] Supervisor program running:
  ```
  php /var/www/cargotaxi/artisan queue:work --sleep=3 --tries=3 --max-time=3600
  ```
  (autostart=true, autorestart=true, numprocs=1+, user=web user)
- [ ] `supervisorctl reread && supervisorctl update && supervisorctl start cargotaxi-worker:*`
- [ ] After any deploy: `php artisan queue:restart`

## 7. Scheduler (if any scheduled commands are used)
- [ ] Cron: `* * * * * cd /var/www/cargotaxi && php artisan schedule:run >> /dev/null 2>&1`

## 8. Google OAuth (web "Continue with Google")
- [ ] In Google Cloud Console (project **ultt-ce8f2**) → Credentials → the Web OAuth client → **Authorised redirect URIs**, add:
  `https://cargotaxi.at/login/google/callback`
- [ ] **OAuth consent screen** → publish to "In production" (or add testers), set app name, support email, **privacy policy URL**.

## 9. Smoke test (production)
- [ ] `https://cargotaxi.at/login` loads over HTTPS.
- [ ] `https://cargotaxi.at/api/v1/languages` returns JSON (no longer 404).
- [ ] Web login works (email/password + Google).
- [ ] Create a booking from the iOS app (Release) → confirm it hits the server.
- [ ] Confirm a booking email actually arrives (proves SMTP + queue worker).
- [ ] `https://cargotaxi.at/telescope` is **404/forbidden** (must NOT be public).

## 10. Hardening
- [ ] `.env`, `storage/`, `composer.*`, `.git/` are NOT web-accessible (docroot = `public/`).
- [ ] Error pages don't leak stack traces (verify `APP_DEBUG=false`).
- [ ] DB backups scheduled.

---

### Optional: make Telescope local-only at the package level
Belt-and-suspenders beyond `TELESCOPE_ENABLED=false` (already enforced in code via
`AppServiceProvider`). In `composer.json`:
```json
"extra": { "laravel": { "dont-discover": ["laravel/telescope"] } }
```
then register it only in `local` from `AppServiceProvider::register()` and
`composer dump-autoload`.
