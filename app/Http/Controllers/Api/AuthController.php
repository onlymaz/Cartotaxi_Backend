<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendConfirmationEmail;
use App\Models\Order;
use App\Models\User;
use App\Utilities\FireBaseRealTimeDatabase;
use App\Utilities\UserHelper;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validation_fields = [
            'email'        => 'required|email|unique:users',
            'first_name'   => 'required',
            'last_name'    => 'required',
            'phone_number' => 'required|unique:users',
            'password'     => ['required', 'min:6', 'confirmed'],
            'lat'          => 'required|numeric|between:-90,90',
            'long'         => 'required|numeric|between:-180,180',
        ];

        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $rawCode = (string) random_int(100000, 999999);

        // role_id and confirmed are intentionally NOT in $fillable. forceCreate
        // bypasses mass-assignment guards — used here because the role and
        // initial verification state are server-controlled, not client input.
        $user = User::forceCreate([
            'role_id'                       => 3,
            'first_name'                    => ucfirst($request['first_name']),
            'last_name'                     => ucfirst($request['last_name']),
            'email'                         => $request['email'],
            'device'                        => $request['device'],
            'provider'                      => 'app',
            'phone_number'                  => $request['phone_number'],
            'password'                      => Hash::make($request['password']),
            'confirmation_code'             => Hash::make($rawCode),
            'confirmation_code_expires_at'  => now()->addMinutes(30),
            // Auto-confirm new signups (email delivery not configured yet).
            // Switch back to 0 once SMTP is set up if email verification is wanted.
            'confirmed'                     => 1,
            'IsActive'                      => 1,
            'fcm_token'                     => '',
            'lat'                           => $request->lat,
            'long'                          => $request->long,
        ]);

        $data['user'] = UserHelper::user_stats($user);
        $object       = UserHelper::user_array($user);
        if (config('mail.from.address')) { $this->dispatch(new SendConfirmationEmail($user, $rawCode)); }
        $reference = 'users/' . $user->id;
        FireBaseRealTimeDatabase::StoreData($reference, $object);

        return response()->json([
            'status'   => true,
            'messages' => 'Successfully created account',
            'data'     => $data
        ], 201);
    }

    public function Login(Request $request)
    {
        $validation_fields = [
            'email'     => 'required|email',
            'password'  => 'required',
            'fcm_token' => 'required',
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $key  = config('app.jwt_secret');
        // Email lookup is case-insensitive: phones routinely capitalize the
        // first letter, which must not lock the user out.
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        // Admin accounts cannot log in via the mobile API
        if (isset($user) && $user->role_id == 1) {
            return response()->json([
                'status'   => false,
                'messages' => 'Admin accounts cannot use the mobile API.',
            ], 403);
        }

        if (!empty($user) && !$user->confirmed) {
            $data_user['user'] = UserHelper::user_stats($user);
            return response()->json([
                'status'   => true,
                'messages' => 'Please confirm your account first',
                'data'     => $data_user
            ], 200);
        }

        $credentials = [
            'email'    => $user->email ?? $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user  = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();
            $now   = now()->timestamp;
            $token = JWT::encode([
                'iss' => config('app.url'),
                'sub' => $user->id,
                'iat' => $now,
                'nbf' => $now,
                'exp' => $now + (int) config('app.jwt_ttl', 86400),
                'jti' => (string) Str::uuid(),
                'email' => $user->email,
                'id'    => $user->id,
            ], $key, 'HS256');
            // access_token is not in $fillable; assign directly so mass-assignment
            // can never set it from request input.
            $user->access_token = $token;
            // Web logins (platform=web) carry no real push token. Don't let a
            // browser sign-in overwrite the user's phone FCM token, which would
            // silently break mobile push notifications for that user.
            if ($request->fcm_token && $request->fcm_token !== 'web' && $request->input('platform') !== 'web') {
                $user->fcm_token = $request->fcm_token;
            }
            // Both apps send their position at login. Persisting it makes a
            // freshly signed-in rider immediately visible to auto-dispatch
            // (which selects riders by users.lat/long + updated_at freshness).
            if (is_numeric($request->lat) && is_numeric($request->long)
                && abs((float) $request->lat) <= 90 && abs((float) $request->long) <= 180) {
                $user->lat  = (string) $request->lat;
                $user->long = (string) $request->long;
            }
            $user->save();
            $data_user['user'] = UserHelper::user_stats($user);
            $data_user['firebase_token'] = $this->issueFirebaseToken($user);
            $object            = UserHelper::user_array($user);
            $reference         = 'users/' . $user->id;
            FireBaseRealTimeDatabase::StoreData($reference, $object);

            return response()->json([
                'status'   => true,
                'messages' => 'Logged in Successfully',
                'data'     => $data_user
            ], 200);
        }

        return response()->json([
            'status'   => false,
            'messages' => 'Incorrect Credentials'
        ], 401);
    }

    public function update_fcm_token(Request $request)
    {
        $validation_fields = [
            'fcm_token' => 'required',
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $user = User::where('email', '=', $request->user()->email)->first();
        $user->update(['fcm_token' => $request->fcm_token]);

        $data_user['user'] = UserHelper::user_stats($user);
        $object            = UserHelper::user_array($user);
        $reference         = 'users/' . $user->id;
        FireBaseRealTimeDatabase::StoreData($reference, $object);

        return response()->json([
            'status'   => true,
            'messages' => 'Fcm Token updated Successfully',
            'data'     => $data_user
        ], 200);
    }

    public function codeConfirmed(Request $request)
    {
        $validation_fields = [
            'email'     => 'required|email',
            'code'      => 'required',
            'fcm_token' => 'required',
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        $invalidCode = static function () {
            return response()->json([
                'status'   => false,
                'messages' => 'Email does not match with code'
            ], 400);
        };

        if (!$user || empty($user->confirmation_code)) {
            return $invalidCode();
        }
        if ($user->confirmation_code_expires_at && now()->greaterThan($user->confirmation_code_expires_at)) {
            return $invalidCode();
        }
        if (!Hash::check((string) $request->code, $user->confirmation_code)) {
            return $invalidCode();
        }

        $now   = now()->timestamp;
        $token = JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + (int) config('app.jwt_ttl', 86400),
            'jti' => (string) Str::uuid(),
            'email' => $user->email,
            'id'    => $user->id,
        ], config('app.jwt_secret'), 'HS256');

        $user->confirmation_code             = null;
        $user->confirmation_code_expires_at  = null;
        $user->confirmed                     = 1;
        $user->fcm_token                     = $request->fcm_token;
        $user->IsActive                      = 1;
        $user->access_token                  = $token;
        $user->save();

        $data_user['user'] = UserHelper::user_stats($user);
        $data_user['firebase_token'] = $this->issueFirebaseToken($user);
        $object            = UserHelper::user_array($user);
        $reference         = 'users/' . $user->id;
        FireBaseRealTimeDatabase::StoreData($reference, $object);

        return response()->json([
            'status'   => true,
            'messages' => 'success',
            'data'     => $data_user
        ]);
    }

    /**
     * Mint a Firebase custom token so the apps can call
     * Auth.auth().signIn(withCustomToken:) and pass the locked-down
     * Realtime Database rules. The Firebase uid equals the backend user id.
     */
    private function issueFirebaseToken(User $user): ?string
    {
        try {
            return app('firebase.auth')->createCustomToken((string) $user->id)->toString();
        } catch (\Throwable $e) {
            // Don't fail login if Firebase is unreachable; the app can retry
            // via GET /api/v1/firebase-token.
            Log::warning('Firebase custom token failed: ' . $e->getMessage());
            return null;
        }
    }

    public function firebaseToken(Request $request)
    {
        $token = $this->issueFirebaseToken($request->user());

        if ($token === null) {
            return response()->json([
                'status'   => false,
                'messages' => 'Could not issue Firebase token',
            ], 503);
        }

        return response()->json([
            'status' => true,
            'data'   => ['firebase_token' => $token],
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->fcm_token     = '';
        $user->access_token  = '';
        $user->fcm_web_token = '';
        $user->save();

        return response()->json([
            'status'   => true,
            'messages' => 'Logged out successfully'
        ]);
    }

    public function DeleteUser(Request $request)
    {
        $user = $request->user();
        if ($user) {
            User::where('id', $user->id)->update([
                'email'         => 'deleted_' . $user->id . '_' . random_int(100000, 999999) . '@deleted.invalid',
                'first_name'    => '',
                'last_name'     => '',
                'phone_number'  => '',
                'fcm_token'     => '',
                'fcm_web_token' => '',
                'access_token'  => '',
                'IsActive'      => '0',
            ]);
        }

        return response()->json([
            'status'   => true,
            'messages' => 'Delete Successfully'
        ]);
    }

    public function ReSendConfirmationCode(Request $request)
    {
        $validation_fields = [
            'email' => 'required|email'
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();
        if ($user) {
            $rawCode = (string) random_int(100000, 999999);
            $user->touch();
            $user->confirmation_code            = Hash::make($rawCode);
            $user->confirmation_code_expires_at = now()->addMinutes(30);
            $user->save();
            if (config('mail.from.address')) { $this->dispatch(new SendConfirmationEmail($user, $rawCode)); }
        }

        // Generic response in both branches to prevent user enumeration.
        return response()->json([
            'status'   => true,
            'messages' => 'If an account exists for this email, a confirmation code has been sent.',
        ], 200);
    }

    public function statistics(Request $request)
    {
        $user = $request->user();

        if ($user->role_id == 3) {
            $totalPayment   = Payment::where('status', 'completed')->where('customer_id', $user->id)->sum('amount');
            $totalOrder     = Order::where('customer_id', $user->id)->count('id');
            $completedOrder = Order::where('customer_id', $user->id)->where('order_status', 'delivered')->count('id');
            $scheduleOrder  = Order::where('customer_id', $user->id)
                ->whereDate('picked_time', '>', Carbon::today())
                ->count('id');

            $data['total_payment']  = floatval(number_format((float)$totalPayment, 2, '.', ''));
            $data['totalOrder']     = $totalOrder;
            $data['completedOrder'] = $completedOrder;
            $data['scheduleOrder']  = $scheduleOrder;

        } elseif ($user->role_id == 2) {
            $completedRide = Order::where('rider_id', $user->id)->where('order_status', 'delivered')->count('id');
            $assignRide    = Order::where('rider_id', $user->id)
                ->where('order_status', '!=', 'delivered')
                ->count('id');

            $data['completed_ride'] = $completedRide;
            $data['assigned_ride']  = $assignRide;
        } else {
            $data = [];
        }

        return response()->json([
            'status'     => true,
            'messages'   => 'User Statistics',
            'user_stats' => $data
        ]);
    }

    /**
     * Legacy `user-object` endpoint: return the caller's fresh profile in the
     * same shape Login uses, so the apps can refresh their cached user.
     */
    public function userObject(Request $request)
    {
        $data['user'] = UserHelper::user_stats($request->user());

        return response()->json([
            'status'   => true,
            'messages' => 'User profile',
            'data'     => $data,
        ]);
    }

    /**
     * Business signup (`business-account`): the regular customer registration
     * plus the company fields the SignupBusiness screen collects.
     */
    public function registerBusiness(Request $request)
    {
        $validation_fields = [
            'email'        => 'required|email|unique:users',
            'first_name'   => 'required',
            'last_name'    => 'required',
            'phone_number' => 'required|unique:users',
            'password'     => ['required', 'min:6', 'confirmed'],
            'lat'          => 'required|numeric|between:-90,90',
            'long'         => 'required|numeric|between:-180,180',
            'company_name' => 'required|string|max:191',
            'vat_number'   => 'nullable|string|max:64',
        ];

        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $rawCode = (string) random_int(100000, 999999);

        $user = User::forceCreate([
            'role_id'                       => 3,
            'first_name'                    => ucfirst($request['first_name']),
            'last_name'                     => ucfirst($request['last_name']),
            'email'                         => $request['email'],
            'device'                        => $request['device'],
            'provider'                      => 'app',
            'phone_number'                  => $request['phone_number'],
            'password'                      => Hash::make($request['password']),
            'confirmation_code'             => Hash::make($rawCode),
            'confirmation_code_expires_at'  => now()->addMinutes(30),
            // Auto-confirm new signups (email delivery not configured yet).
            // Switch back to 0 once SMTP is set up if email verification is wanted.
            'confirmed'                     => 1,
            'IsActive'                      => 1,
            'fcm_token'                     => '',
            'lat'                           => $request->lat,
            'long'                          => $request->long,
            'company_name'                  => $request->company_name,
            'vat_number'                    => $request->vat_number,
        ]);

        $data['user'] = UserHelper::user_stats($user);
        $object       = UserHelper::user_array($user);
        if (config('mail.from.address')) { $this->dispatch(new SendConfirmationEmail($user, $rawCode)); }
        FireBaseRealTimeDatabase::StoreData('users/' . $user->id, $object);

        return response()->json([
            'status'   => true,
            'messages' => 'Successfully created account',
            'data'     => $data
        ], 201);
    }

    /**
     * Social sign-in (`social-login`). The app sends the provider token in
     * `access_token` and the provider name in `action` (apple | facebook).
     * The token is verified server-side with the provider before any account
     * is created or signed in — the client's word alone is never enough.
     */
    public function socialLogin(Request $request)
    {
        $validation_fields = [
            'access_token' => 'required|string',
            'action'       => 'required|in:apple,facebook,google',
            'fcm_token'    => 'required',
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        try {
            $identity = $this->verifySocialToken($request->action, $request->access_token);
        } catch (\Throwable $e) {
            Log::warning('Social login verification failed (' . $request->action . '): ' . $e->getMessage());
            return response()->json([
                'status'   => false,
                'messages' => 'Could not verify your ' . ucfirst($request->action) . ' sign-in. Please try again.',
            ], 401);
        }

        $user = User::where('provider', $request->action)
            ->where('provider_id', $identity['id'])
            ->first();

        if (!$user && !empty($identity['email'])) {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($identity['email'])])->first();
        }

        // Social sign-in (Google/Apple) is for CUSTOMERS only. Admins (role 1)
        // and drivers/riders (role 2) must not be able to sign into the
        // customer app this way — they have the admin panel / driver app.
        if ($user && (int) $user->role_id !== 3) {
            return response()->json([
                'status'   => false,
                'messages' => (int) $user->role_id === 1
                    ? 'Admin accounts cannot use the mobile API.'
                    : 'This account is registered as a driver. Please use the driver app to sign in.',
            ], 403);
        }

        if (!$user) {
            $user = User::forceCreate([
                'role_id'      => 3,
                'first_name'   => ucfirst($request->input('first_name', $identity['first_name'] ?? 'CargoTaxi')),
                'last_name'    => ucfirst($request->input('last_name', $identity['last_name'] ?? 'Customer')),
                'email'        => $identity['email'] ?? ($request->action . '-' . $identity['id'] . '@social.cargotaxi.local'),
                'device'       => $request->input('device', 'ios'),
                'provider'     => $request->action,
                'provider_id'  => $identity['id'],
                'phone_number' => null,
                'password'     => Hash::make(Str::random(40)),
                'confirmed'    => 1,
                'IsActive'     => 1,
                'fcm_token'    => '',
                'lat'          => $request->input('lat'),
                'long'         => $request->input('long'),
            ]);
        } elseif (empty($user->provider_id)) {
            // Existing email account signing in socially for the first time.
            $user->provider     = $request->action;
            $user->provider_id  = $identity['id'];
        }

        if (!$user->IsActive && $user->confirmed) {
            return response()->json([
                'status'   => false,
                'messages' => 'Your account has been disabled. Please contact support.',
            ], 403);
        }

        $now   = now()->timestamp;
        $token = JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + (int) config('app.jwt_ttl', 86400),
            'jti' => (string) Str::uuid(),
            'email' => $user->email,
            'id'    => $user->id,
        ], config('app.jwt_secret'), 'HS256');

        $user->access_token = $token;
        $user->fcm_token    = $request->fcm_token;
        $user->confirmed    = 1;
        $user->IsActive     = 1;
        $user->save();

        $data['user'] = UserHelper::user_stats($user);
        FireBaseRealTimeDatabase::StoreData('users/' . $user->id, UserHelper::user_array($user));

        return response()->json([
            'status'   => true,
            'messages' => 'Logged in Successfully',
            'data'     => $data,
        ], 200);
    }

    /**
     * Verify a provider token and return ['id', 'email'?, 'first_name'?, 'last_name'?].
     *
     * @throws \RuntimeException when the token cannot be verified
     */
    private function verifySocialToken(string $provider, string $token): array
    {
        if ($provider === 'apple') {
            // The app sends Apple's identity token (a JWT). Verify its
            // signature against Apple's published JWKS.
            $jwks = json_decode(file_get_contents('https://appleid.apple.com/auth/keys'), true);
            $claims = (array) JWT::decode($token, \Firebase\JWT\JWK::parseKeySet($jwks));

            if (($claims['iss'] ?? '') !== 'https://appleid.apple.com') {
                throw new \RuntimeException('Unexpected Apple token issuer');
            }
            $expectedAud = config('services.apple.client_id');
            if (!empty($expectedAud) && ($claims['aud'] ?? '') !== $expectedAud) {
                throw new \RuntimeException('Apple token audience mismatch');
            }

            return [
                'id'    => $claims['sub'],
                'email' => $claims['email'] ?? null,
            ];
        }

        if ($provider === 'google') {
            $expectedAudience = (string) config('services.google.client_id');
            if ($expectedAudience === '') {
                throw new \RuntimeException('Google OAuth client ID is not configured');
            }

            $jwks = Cache::remember('google-oauth-jwks', now()->addMinutes(5), function () {
                $json = @file_get_contents('https://www.googleapis.com/oauth2/v3/certs');
                $keys = $json === false ? null : json_decode($json, true);
                if (!is_array($keys) || empty($keys['keys'])) {
                    throw new \RuntimeException('Could not load Google signing keys');
                }

                return $keys;
            });

            // The Android app sends GoogleSignInAccount.idToken. Verify its
            // signature and expiry locally, then pin issuer + audience to this
            // backend's web OAuth client before trusting the identity claims.
            $claims = (array) JWT::decode($token, \Firebase\JWT\JWK::parseKeySet($jwks));
            $issuer = $claims['iss'] ?? '';
            $audiences = (array) ($claims['aud'] ?? []);
            if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)
                || !in_array($expectedAudience, $audiences, true)
                || empty($claims['sub'])
                || empty($claims['email'])
                || filter_var($claims['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN) !== true) {
                throw new \RuntimeException('Google ID token claims rejected');
            }

            return [
                'id'         => $claims['sub'],
                'email'      => $claims['email'],
                'first_name' => $claims['given_name'] ?? null,
                'last_name'  => $claims['family_name'] ?? null,
            ];
        }

        if ($provider === 'facebook') {
            $url = 'https://graph.facebook.com/me?fields=id,email,first_name,last_name&access_token=' . urlencode($token);

            $response = json_decode(file_get_contents($url), true);
            $id = $response['id'] ?? $response['sub'] ?? null;
            if (empty($id)) {
                throw new \RuntimeException(ucfirst($provider) . ' token rejected');
            }

            return [
                'id'         => $id,
                'email'      => $response['email'] ?? null,
                'first_name' => $response['first_name'] ?? $response['given_name'] ?? null,
                'last_name'  => $response['last_name'] ?? $response['family_name'] ?? null,
            ];
        }

        throw new \RuntimeException('Unsupported provider');
    }
}
