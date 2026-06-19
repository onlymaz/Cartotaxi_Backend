# Deploying the CargoTaxi backend to cargotaxi.at

_Last verified locally: 2026-06-12 — all 20 tests pass; login returns `firebase_token`;
FCM v1 push path authorized; RTDB rules require auth._

The iOS apps' production config points to **`https://cargotaxi.at/api/v1/`**.
This guide puts the Laravel app there.

## Server requirements
- PHP **8.3+** (8.4 recommended — local dev uses `/opt/homebrew/opt/php@8.4`)
- Extensions: pdo_mysql, mbstring, openssl, curl, gd, bcmath, ctype, json, tokenizer, xml
- MySQL 8 / MariaDB 10.6+
- Composer 2
- HTTPS for the domain (Let's Encrypt is fine)

## 1. Upload the code
Upload the entire `Share_Folder` project (rename it, e.g. `cargotaxi-api`) to the
server — but NOT into the public web root. Exclude: `node_modules/`, `.env`,
`storage/logs/*`, `database/*.sqlite`.

**Web root must point at the `public/` directory only.**
- VPS (nginx/apache): set the vhost document root to `/path/to/cargotaxi-api/public`
- Shared hosting (e.g. Hostinger): if you cannot change the document root, ask
  support to point the domain's root at `public/`, or use a subdomain
  `api.cargotaxi.at` whose root is `public/` (then update the iOS configs to
  `https://api.cargotaxi.at/api/v1/` — tell Claude, it's a 2-line change).

> Note: `cargotaxi.at` currently serves a website at `/`. If you want to KEEP that
> site, deploy the API on `api.cargotaxi.at` instead — cleanest option.

## 2. Install dependencies (on the server)
```bash
cd /path/to/cargotaxi-api
composer install --no-dev --optimize-autoloader
```

## 3. Configure environment
```bash
cp .env.production.example .env
nano .env            # fill every <...> value (DB, SMTP, JWT_SECRET)
php artisan key:generate
```
Upload the Firebase service account file to
`storage/app/firebase/service-account.json` (copy it from the same path in this
repo — it is NOT in git history, transfer it securely, never through chat/email).

## 4. Database
```bash
php artisan migrate --force
php artisan db:seed --force        # only on first deploy, if seeders set up roles/settings
```

## 5. Laravel housekeeping
```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
chmod -R ug+rwx storage bootstrap/cache
```

## 6. Queue worker (required — emails + notifications use QUEUE_CONNECTION=database)
- VPS: add a supervisor/systemd entry running `php artisan queue:work --tries=3`
- Shared hosting: add a cron entry every minute:
  `* * * * * php /path/to/cargotaxi-api/artisan queue:work --stop-when-empty`

## 7. Smoke test (from your Mac)
```bash
curl -s https://cargotaxi.at/api/v1/languages            # expect JSON, not 404
curl -s -X POST https://cargotaxi.at/api/v1/login \
  -H "Accept: application/json" \
  -F email=<real user> -F password=<pwd> -F fcm_token=test -F lat=48.2 -F long=16.3
# expect: {"status":true,...,"firebase_token":"eyJ..."}
```
Then build the user app with `APP_ENV=PRODUCTION` and do a real login on a device.

## Security checklist
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `.env` and `storage/` are NOT web-accessible (only `public/` is the web root)
- [ ] Fresh `JWT_SECRET` (do not reuse the local dev one)
- [ ] HTTPS enforced (HTTP → HTTPS redirect)
- [ ] `service-account.json` outside web root, permissions 600
