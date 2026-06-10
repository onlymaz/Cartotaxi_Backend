<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    /**
     * The OAuth providers the application accepts. Anything outside this
     * allowlist is rejected up front to prevent attackers from probing
     * arbitrary Socialite drivers via the URL.
     */
    private const ALLOWED_PROVIDERS = ['google', 'facebook'];

    public function redirectToProvider($provider)
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            // Never echo the upstream exception back to the client; log internally
            // and show a generic message so we don't leak provider-side error
            // detail (URLs, tokens, internal class names).
            Log::warning('SocialLogin: provider error', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            return redirect('/login')->with('message', 'Login failed. Please try again.');
        }

        $providerId = (string) $socialUser->getId();
        $email      = $socialUser->getEmail();

        // 1. Match on (provider, provider_id) first — this is the only reliable
        //    "same human across logins" signal. Email-only matching is unsafe
        //    because some providers don't verify email ownership.
        $user = User::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        // 2. Fall back to linking by email, but ONLY if the email is non-null
        //    AND no other user has already claimed this provider+id pair. We do
        //    NOT silently merge a fresh OAuth identity into an existing
        //    password-registered account — that's the takeover vector.
        if (!$user && !empty($email)) {
            $existing = User::where('email', $email)->first();
            if ($existing) {
                if (empty($existing->provider) && empty($existing->provider_id)) {
                    // Account exists with this email but has never been linked.
                    // Refuse to auto-link; force the user to log in with their
                    // password first and link from inside the app.
                    Log::info('SocialLogin: refused to auto-link', [
                        'provider' => $provider,
                        'user_id'  => $existing->id,
                    ]);
                    return redirect('/login')->with(
                        'message',
                        'An account with this email already exists. Please log in with your password to link a social account.'
                    );
                }
                // Already linked, but to a different provider; refuse.
                if ($existing->provider !== $provider || (string) $existing->provider_id !== $providerId) {
                    return redirect('/login')->with('message', 'Login failed. Please try again.');
                }
                $user = $existing;
            }
        }

        // 3. No match anywhere → create a new user. Use a non-loginable random
        //    password (the user can never know it) so password-form logins to
        //    a social-only account fail closed.
        if (!$user) {
            $name      = $socialUser->getName() ?: '';
            $firstName = $name;
            $lastName  = '';
            if (strpos($name, ' ') !== false) {
                [$firstName, $lastName] = explode(' ', $name, 2);
            }

            // role_id, provider_id, email_verified_at and confirmed are not in
            // $fillable. forceCreate is the documented escape hatch for the
            // legitimate, server-controlled set.
            $user = User::forceCreate([
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'email'             => $email ?: ($providerId . '@' . $provider . '.invalid'),
                'provider'          => $provider,
                'provider_id'       => $providerId,
                'password'          => Hash::make(Str::random(64)), // unguessable, never disclosed
                'email_verified_at' => $email ? now() : null,
                'profile_image'     => $socialUser->getAvatar(),
                'role_id'           => 3, // customer
                'IsActive'          => 1,
                'confirmed'         => $email ? 1 : 0,
            ]);
        }

        Auth::login($user, true);
        session()->save();

        Log::info('SocialLogin: user logged in', ['user_id' => $user->id, 'provider' => $provider]);

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
        } elseif ($user->hasRole('rider')) {
            return redirect()->route('rider.dashboard');
        }

        return redirect('/');
    }
}
