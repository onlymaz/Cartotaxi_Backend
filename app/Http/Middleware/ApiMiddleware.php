<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;          // Required by firebase/php-jwt v6+
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key           = config('app.jwt_secret');
        $authorization = $request->header('Authorization');

        if (empty($authorization)) {
            return $this->unauthorised();
        }

        $parts = explode('Bearer ', $authorization);
        if (count($parts) !== 2 || $parts[0] !== '') {
            return $this->unauthorised();
        }

        $token = $parts[1];

        // Production previously issued opaque bearer tokens. Existing app
        // installs keep that value in SharedPreferences across APK upgrades,
        // so rejecting every non-JWT token breaks the first protected action
        // (normally booking) even though the user has a valid saved session.
        // Accept only an exact, non-empty token still stored for an active
        // account, and return a signed JWT for the upgraded Android app to
        // persist. Keep the stored legacy token during the migration so older
        // installed builds continue working until users update.
        if (substr_count($token, '.') !== 2) {
            $legacyUser = strlen($token) >= 20
                ? User::where('access_token', $token)->where('IsActive', 1)->first()
                : null;

            if (!$legacyUser) {
                Log::warning('Legacy bearer token rejected');
                return $this->unauthorised();
            }

            $request->merge(['user' => $legacyUser]);
            $request->setUserResolver(function () use ($legacyUser) {
                return $legacyUser;
            });

            $response = $next($request);
            $response->headers->set('X-Access-Token', $this->issueJwt($legacyUser, $key));
            return $response;
        }

        try {
            // firebase/php-jwt v6+ requires a Key object instead of a bare string + array
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            if ($decoded) {
                $user = User::where('access_token', $token)->first();

                // Allow multiple concurrent sessions (app + website + multiple
                // devices). A validly-signed, non-expired JWT resolves by its
                // subject/email even if a later login rotated the persisted
                // access_token column — so logging in elsewhere no longer kicks
                // out an existing session.
                if (!$user) {
                    $decodedUserId = isset($decoded->sub) ? (int) $decoded->sub : 0;
                    $decodedEmail = isset($decoded->email) ? (string) $decoded->email : '';

                    if ($decodedUserId > 0) {
                        $user = User::where('id', $decodedUserId)
                            ->when($decodedEmail !== '', function ($query) use ($decodedEmail) {
                                return $query->where('email', $decodedEmail);
                            })
                            ->first();
                    }
                }

                if ($user) {
                    $request->merge(['user' => $user]);
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });
                    return $next($request);
                }
            }
        } catch (ExpiredException $e) {
            Log::info('JWT expired for token');
        } catch (SignatureInvalidException $e) {
            Log::warning('JWT signature invalid');
        } catch (BeforeValidException $e) {
            Log::warning('JWT not yet valid');
        } catch (\Exception $e) {
            Log::warning('JWT decode failed: ' . $e->getMessage());
        }

        return $this->unauthorised();
    }

    private function unauthorised(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'   => false,
            'messages' => 'Unauthorized',
        ], 401);
    }

    private function issueJwt(User $user, string $key): string
    {
        $now = now()->timestamp;

        return JWT::encode([
            'iss'   => config('app.url'),
            'sub'   => $user->id,
            'iat'   => $now,
            'nbf'   => $now,
            'exp'   => $now + (int) config('app.jwt_ttl', 86400),
            'jti'   => (string) Str::uuid(),
            'email' => $user->email,
            'id'    => $user->id,
        ], $key, 'HS256');
    }
}
