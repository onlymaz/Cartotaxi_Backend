<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // MariaDB / older MySQL chokes on Laravel's default 191-char index
        // length under utf8mb4. No-op on modern engines; cheap insurance.
        Schema::defaultStringLength(191);

        // Force https:// URL generation when APP_URL is https — Mailables and
        // queue jobs need absolute URLs that survive a reverse proxy that
        // strips the scheme.
        if (is_string(config('app.url')) && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Production safety net: refuse to boot if FRONTEND_URL still points
        // at localhost. config/cors.php derives allowed_origins from this
        // value with supports_credentials=true, so a stale dev value would
        // let a developer browser carry credentialed CORS requests in prod.
        $env      = config('app.env');
        $frontend = (string) env('FRONTEND_URL', '');
        $isLocalish = $frontend === ''
            || str_contains($frontend, 'localhost')
            || str_contains($frontend, '127.0.0.1');

        // Skip the guard inside `php artisan` so a misconfigured local env
        // doesn't block migrate / queue:work / cache:clear. The check fires
        // only when the app is actually about to serve HTTP traffic.
        if ($env === 'production' && $isLocalish && !$this->app->runningInConsole()) {
            throw new \RuntimeException(
                'Refusing to boot: APP_ENV=production but FRONTEND_URL is unset or points at localhost. '
                . 'Set FRONTEND_URL to your real frontend origin(s) before serving production traffic.'
            );
        }
    }
}
