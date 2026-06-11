<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The shipped iOS apps (User + Driver) talk the legacy API surface:
 * multipart POSTs to paths like `pending-orders`, `accept-order` and
 * `update-coordinates`. These tests pin the compatibility aliases so a
 * route refactor can't silently strand the apps again.
 */
class LegacyEndpointCompatTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function rider_dispatch_aliases_respond()
    {
        [$rider, $token] = $this->apiUser(['role_id' => 2]);
        $headers = ['Authorization' => 'Bearer ' . $token];

        // Offers list (Driver app polls this for incoming calls)
        $this->withHeaders($headers)->postJson('/api/v1/pending-orders')
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        // GPS heartbeat — what makes a rider "available" for dispatch
        $this->withHeaders($headers)->postJson('/api/v1/update-coordinates', [
            'lat' => 48.2085, 'long' => 16.3731,
        ])->assertStatus(200)->assertJson(['status' => true]);

        // The heartbeat must land in users.lat/long: that's what the
        // assign:rider cron reads to pick the nearest rider.
        $rider->refresh();
        $this->assertSame('48.2085', $rider->lat);
        $this->assertSame('16.3731', $rider->long);

        // Accept/reject validation runs (no offer exists, so invalid order)
        $this->withHeaders($headers)->postJson('/api/v1/accept-order', [
            'order_id' => 999999, 'status' => 'rejected',
        ])->assertStatus(200);
    }

    /** @test */
    public function profile_aliases_respond()
    {
        [$user, $token] = $this->apiUser([]);
        $headers = ['Authorization' => 'Bearer ' . $token];

        $this->withHeaders($headers)->postJson('/api/v1/user-object')
            ->assertStatus(200)
            ->assertJsonPath('data.user.email', $user->email);

        $this->withHeaders($headers)->postJson('/api/v1/user-statistics')
            ->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    /** @test */
    public function bookings_post_returns_the_list_not_a_new_order()
    {
        [, $token] = $this->apiUser([]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/bookings');

        $response->assertStatus(200)->assertJson(['status' => true]);
        $this->assertSame(0, \App\Models\Order::count(), 'POST bookings must not create an order');
    }

    /** @test */
    public function business_account_registers_a_company_customer()
    {
        $this->postJson('/api/v1/business-account', [
            'email'                 => 'firma@example.com',
            'first_name'            => 'Maria',
            'last_name'             => 'Huber',
            'phone_number'          => '+43155512345',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'lat'                   => 48.2,
            'long'                  => 16.37,
            'company_name'          => 'Huber Logistik GmbH',
            'vat_number'            => 'ATU12345678',
        ])->assertStatus(201)->assertJson(['status' => true]);

        $this->assertDatabaseHas('users', [
            'email'        => 'firma@example.com',
            'company_name' => 'Huber Logistik GmbH',
            'role_id'      => 3,
        ]);
    }

    /** @test */
    public function stripe_intent_returns_unconfigured_without_keys()
    {
        [, $token] = $this->apiUser([]);
        config(['services.stripe.secret' => null]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/v1/payments/stripe/payment-intent')
            ->assertStatus(503);
    }

    private function apiUser(array $overrides): array
    {
        $user = User::forceCreate(array_merge([
            'first_name'   => 'Test',
            'last_name'    => 'User',
            'email'        => Str::uuid() . '@example.com',
            'password'     => Hash::make('password'),
            'role_id'      => 3,
            'phone_number' => Str::random(10),
            'IsActive'     => 1,
            'confirmed'    => 1,
        ], $overrides));

        $now = now()->timestamp;
        $token = JWT::encode([
            'iss' => 'phpunit',
            'sub' => $user->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 3600,
            'jti' => (string) Str::uuid(),
            'email' => $user->email,
            'id'    => $user->id,
        ], config('app.jwt_secret'), 'HS256');

        $user->access_token = $token;
        $user->save();

        return [$user, $token];
    }
}
