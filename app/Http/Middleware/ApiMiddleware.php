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

        try {
            // firebase/php-jwt v6+ requires a Key object instead of a bare string + array
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            if ($decoded) {
                $user = User::where('access_token', $token)->first();

                // Local smoke/debug sessions often log in from both the app and
                // shell tools. In local only, allow a freshly signed JWT to
                // resolve by subject/email even if another debug login rotated
                // the persisted access_token column.
                if (!$user && app()->environment('local')) {
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
}
