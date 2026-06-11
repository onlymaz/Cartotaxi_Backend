<?php

namespace Tests\Feature\Booking;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression test for P0-3: booking total_amount is server-computed from
 * Package.fixed_price + per_km_charges * km. The client cannot dictate the
 * billed amount.
 */
class BookingTotalAmountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_supplied_total_amount_is_ignored()
    {
        // Build the minimal world: a customer, a package with known prices,
        // and a JWT good enough to pass ApiMiddleware (we use the access_token
        // column lookup it performs after JWT verification).
        $customer = User::forceCreate([
            'first_name'   => 'Cust',
            'last_name'    => 'Omer',
            'email'        => 'cust@example.com',
            'password'     => Hash::make('pw'),
            'role_id'      => 3,
            'phone_number' => '5550000',
            'IsActive'     => 1,
            'confirmed'    => 1,
        ]);

        $package = Package::create([
            'name'           => 'Standard',
            'weight'         => 5,
            'unit'           => 'kg',
            'per_km_charges' => 2.50, // €2.50/km
            'fixed_price'    => 10.00, // €10 base
        ]);

        // Mint a token and persist on the user — the middleware checks
        // users.access_token after decoding the JWT.
        $now = now()->timestamp;
        $token = \Firebase\JWT\JWT::encode([
            'iss' => 'phpunit',
            'sub' => $customer->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 3600,
            'jti' => (string) \Illuminate\Support\Str::uuid(),
            'email' => $customer->email,
            'id'    => $customer->id,
        ], config('app.jwt_secret'), 'HS256');
        $customer->access_token = $token;
        $customer->save();

        $payload = [
            'picked_time'    => now()->addHour()->toDateTimeString(),
            'package_id'     => $package->id,
            'transaction_id' => 'tx_test_123',
            'payment_type'   => 'card',
            'poly_points'    => 'enc_polyline',
            'with_helper'    => false,
            'location'       => json_encode([[
                'start_location' => 'A', 'end_location' => 'B',
                'start_lat' => 0.0, 'start_long' => 0.0,
                'end_lat' => 0.0, 'end_long' => 0.0,
            ]]),
            'total_meter'    => 4000, // 4 km → €10 + 4 * €2.50 = €20
            'total_second'   => 600,
            'total_amount'   => 0.01, // <-- client tries to pay 1 cent
            'response'       => '{}',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/booking-store/v2', $payload);

        $response->assertStatus(200)->assertJson(['status' => true]);

        // Server-side computation: 10 + 4 * 2.5 = 20.
        $order = Order::first();
        $this->assertNotNull($order, 'Order should have been created');
        $this->assertEquals(20.00, (float) $order->total_amount,
            'total_amount must be server-computed; client value of 0.01 must be ignored');
        $this->assertEquals('processing', $order->order_status);
    }
}
