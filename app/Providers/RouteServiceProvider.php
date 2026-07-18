<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        parent::boot();
    }

    /**
     * Protect password login without making every customer behind the same
     * office/home/mobile-carrier NAT share a five-attempt allowance.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', 'missing-email')));
            $response = function (Request $request, array $headers) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Too many login attempts. Please wait one minute and try again.',
                ], 429, $headers);
            };

            return [
                // Stops focused attacks against one account, even when the
                // attacker rotates source IP addresses.
                Limit::perMinute(10)
                    ->by('login-account:' . sha1($email))
                    ->response($response),

                // Still caps broad credential-stuffing from one source while
                // allowing many legitimate accounts on shared networks.
                Limit::perMinute(60)
                    ->by('login-ip:' . $request->ip())
                    ->response($response),
            ];
        });

        RateLimiter::for('password-reset-request', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', 'missing-email')));
            $response = function (Request $request, array $headers) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Too many password reset requests. Please wait 15 minutes and try again.',
                ], 429, $headers);
            };

            return [
                // Prevent repeated reset emails for one account without making
                // unrelated customers on the same network share the allowance.
                Limit::perMinutes(15, 5)
                    ->by('password-reset-request-account:' . sha1($email))
                    ->response($response),

                Limit::perHour(30)
                    ->by('password-reset-request-ip:' . $request->ip())
                    ->response($response),
            ];
        });

        RateLimiter::for('password-reset-confirm', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', 'missing-email')));
            $response = function (Request $request, array $headers) {
                return response()->json([
                    'status' => false,
                    'messages' => 'Too many password reset attempts. Please wait 15 minutes and try again.',
                ], 429, $headers);
            };

            return [
                Limit::perMinutes(15, 10)
                    ->by('password-reset-confirm-account:' . sha1($email))
                    ->response($response),

                Limit::perHour(60)
                    ->by('password-reset-confirm-ip:' . $request->ip())
                    ->response($response),
            ];
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));


    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
