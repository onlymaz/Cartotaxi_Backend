# CargoTaxi — Production Readiness (Backend + iOS User App + iOS Driver App)

_Last updated: 2026-06-11_

## System overview

| Piece | Location | Status |
|---|---|---|
| Laravel backend (API + admin) | `~/Desktop/Share_Folder` | ✅ Ready pending deploy config |
| iOS User app | `~/Desktop/Projects/iOS_User_App` | ⚠️ Code ready; build blocked by local Xcode platform (see below) |
| iOS Driver app | `~/Desktop/Projects/iOS_CargoTaxi_Driver` | ⚠️ Same |
| `~/Desktop/iOS_User_App-main` | stale copy of the User app | ❌ Ignore/archive — work happens in `Projects/iOS_User_App` |

## What was fixed for production (this pass)

1. **Legacy API compatibility layer** — both iOS apps were built against the
   original API paths (`pending-orders`, `accept-order`, `update-coordinates`,
   `update-booking-status`, `store-rating`, `reset-password`, …) which the
   modernized backend had renamed or dropped. All aliases now route to the same
   controllers as their modern counterparts. `POST /bookings` returns the
   booking list (legacy contract; apps create orders via `booking-store/v2`).
2. **Missing endpoints implemented**:
   - `social-login` — Sign in with Apple (JWT verified against Apple JWKS;
     pin audience with `APPLE_CLIENT_ID`), Facebook & Google token verification.
   - `business-account` — business customer registration (company + VAT).
   - `payments/stripe/payment-intent` (+`/confirm`, `/cancel`) — server-side
     booking + Stripe PaymentIntent using the order's server-side amount.
     Returns 503 until `STRIPE_KEY`/`STRIPE_SECRET` are set.
3. **Dispatch-critical GPS fix** — rider heartbeats now persist to
   `users.lat/long` (previously Firebase-only, so auto-dispatch could never
   find any rider).
4. **Uber-style dispatch** (previous passes): nearest-rider offers, 1-minute
   answer window, forward-only transfer chain, full audit log (admin →
   Dispatch Logs), admin escalation when all riders are exhausted.
5. Test suite: **20/20 passing**, including new legacy-API contract tests.

Verified end-to-end over HTTP (the exact calls the Driver app makes):
login → GPS heartbeat → customer booking → cron offers nearest rider →
`pending-orders` shows the call → `accept-order` assigns → dispatch log row.

## Deploy runbook (backend)

```sh
# server requirements: PHP >= 8.3, MySQL/Postgres, composer, supervisor
git clone <repo> && cd <app>
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
# fill .env (see checklist below), then:
php artisan migrate --force
php artisan db:seed --class=ServiceZonePolygonSeeder   # Vienna + Lower Austria zones
php artisan config:cache && php artisan route:cache && php artisan view:cache
# REQUIRED for auto-dispatch + queued mail:
# crontab:    * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
# supervisor: php artisan queue:work --tries=3
```

### .env production checklist
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<your-domain>`
- `DB_*` → MySQL/Postgres (not SQLite)
- `JWT_SECRET` → long random value (rotate from dev!)
- `GOOGLE_MAPS_API_KEY` → production key, HTTP-referrer + API restricted
- `FIREBASE_*` / `GoogleService-Info` server credentials for FCM + RTDB
- `CURRENCY=EUR`, `CURRENCY_SYMBOL=€`
- `STRIPE_KEY` / `STRIPE_SECRET` (live keys) — card payments
- `APPLE_CLIENT_ID` = iOS bundle id — locks Sign in with Apple tokens
- `PAYPAL_*` production client id, `PAYPAL_LIVE` ≠ sandbox
- `MAIL_*` real SMTP; `FRONTEND_URL` for CORS
- Real Sentry DSN recommended

## iOS apps — before App Store submission

- **Point at production**: User app `Config.local.plist` → `BASE_URL`;
  Driver app `Config.plist` → `API_BASE_URL` / `IMAGE_BASE_URL`
  (both currently fall back to `https://staging.cargotaxi.at`).
- Production `GoogleService-Info.plist` for each app (the Driver app has one
  committed in the repo — replace with the production one and keep it out of git).
- Google Maps iOS key restricted to each app's bundle id.
- Signing: distribution certificates + provisioning profiles (the `Certificates/`
  folders were removed from git on purpose — keep them local).
- APNs auth key (.p8) uploaded to the Firebase project for push.
- Driver app: background-location entitlement + App Store review notes for it.

## Known build blocker (local machine, not the apps)

Xcode was updated to a version whose iOS 26.5 platform/simulator is **not
installed**, and the disk has only ~4.8 GB free (download needs 8.49 GB).
`/Library/Developer/CoreSimulator/Volumes` holds 23.6 GB of **older runtimes
that no longer work with this Xcode** (26.3.1 / 26.4 / 26.4.1).

Fix (user decision — frees space by deleting re-downloadable runtimes):
```sh
xcrun simctl runtime list                  # see runtime UUIDs
xcrun simctl runtime delete <UUID>         # delete old 26.3.1 (and 26.4)
xcodebuild -downloadPlatform iOS           # install the 26.5 platform
```
Then both apps build with the standard commands (see each repo's docs).

## Remaining risks / follow-ups

- Laravel 8 framework is EOL — dependencies were patched to the latest 8.x-
  compatible versions, but a Laravel 10/11 upgrade is still the long-term fix.
- The Stripe create-intent flow trusts the booking's computed `total_amount`
  from the order record (server-side), but the booking totals themselves are
  client-supplied (`total_meter`, `total_amount` in the booking payload) —
  consider server-side fare recalculation as a follow-up hardening step.
- Social login (Facebook/Google) verifies tokens with the providers over
  HTTPS; Apple flow additionally pins issuer (and audience when
  `APPLE_CLIENT_ID` is set).
