# Production Deployment Checklist

Status as of 2026-06-10: code-level blockers fixed (security advisories cleared,
tests green, secrets out of views, git history clean). The items below must be
done **on/for the production server** before go-live.

## 1. Runtime

- [ ] **PHP 8.1** on the server (Laravel 8's supported ceiling). The dev Mac runs
      PHP 8.5, which works but spews deprecation warnings and required
      `--ignore-platform-req=php` for `phpoffice/phpspreadsheet`. CI is pinned
      to 8.1 — match it in production.
- [ ] `composer install --no-dev --optimize-autoloader` (never install dev
      packages — Telescope, Ignition, Faker must not be on the box).

## 2. Environment (`.env` on the server — never commit it)

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false` — debug mode leaks stack traces, env values, and SQL
- [ ] `APP_URL=https://<real-domain>`
- [ ] `APP_KEY` — generate fresh on the server: `php artisan key:generate`
- [ ] `DB_CONNECTION=mysql` (or pgsql) with a real database — **not sqlite**
- [ ] `JWT_SECRET` — generate fresh, **minimum 32 bytes** (php-jwt v7 hard-fails
      shorter HS256 keys): `php -r "echo base64_encode(random_bytes(33));"`
      ⚠ Rotating it invalidates all logged-in mobile sessions — schedule it.
- [ ] `GOOGLE_MAPS_API_KEY` / `FIREBASE_API_KEY` — set real values. These keys
      were hardcoded in views until 2026-06-10 and are browser-exposed by
      design: **add HTTP-referrer restrictions** in Google Cloud console (and
      rotate if the old folder was ever shared).
- [ ] `SENTRY_LARAVEL_DSN` — set so errors are actually reported
- [ ] `MAIL_*`, `FIREBASE_CREDENTIALS` (service-account JSON path, outside web
      root), Twilio/Sofort credentials as applicable
- [ ] Remove/leave empty what's unused (AWS, Pusher) rather than dummy values

## 3. Process & infrastructure

- [ ] **Queue worker** — `QUEUE_CONNECTION=database` needs a running worker:
      supervisor program `php artisan queue:work --tries=3 --max-time=3600`
- [ ] **Scheduler** — cron entry: `* * * * * php artisan schedule:run`
      (app has console commands for order assignment/expiry that depend on it)
- [ ] HTTPS only; redirect HTTP; HSTS at the web server
- [ ] Web root points at `public/` only
- [ ] `storage/` and `bootstrap/cache/` writable by the app user (755/775,
      not 777)

## 4. Deploy steps (each release)

```sh
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache route:cache view:cache event:cache
php artisan queue:restart
php artisan up
```

Note: `config:cache` makes `env()` return null outside config files — views
already use `config()` (fixed 2026-06-10), keep it that way.

## 5. Known accepted risks (review each release)

- **Laravel 8 is EOL.** CVE-2025-27515 (file-validation bypass, medium) is
  unfixable on 8.x and documented as a composer policy exception in
  `composer.json`. Mitigated by strict FormRequest `mimes`+`max` rules on all
  uploads. The real fix is the framework major upgrade (8 → 10/11) — treat as
  the next engineering project.
- 7 abandoned packages (`composer audit` reports them): fruitcake/laravel-cors
  (built into Laravel 9+), swiftmailer (→ symfony/mailer in Laravel 9+),
  laravelcollective/html, etc. All resolve naturally with the framework upgrade.

## 6. Verification after deploy

- [ ] `php artisan config:show app | grep -E 'env|debug'` → production / false
- [ ] Login from the mobile app (JWT issue + authenticated request)
- [ ] Create a booking end-to-end; confirm queue jobs process
- [ ] Trigger a test error; confirm it lands in Sentry
- [ ] `composer audit --abandoned=report` → exit 0
