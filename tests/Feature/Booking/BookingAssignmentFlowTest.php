<?php

namespace Tests\Feature\Booking;

use App\Models\Order;
use App\Models\OrderAssign;
use App\Models\Package;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingAssignmentFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function processing_mobile_booking_is_eligible_for_auto_assignment()
    {
        [$customer, $customerToken] = $this->apiUser([
            'first_name' => 'Cust',
            'last_name' => 'Omer',
            'email' => 'customer@example.com',
            'role_id' => 3,
        ]);
        $rider = $this->apiUser([
            'first_name' => 'Demo',
            'last_name' => 'Driver',
            'email' => 'rider@example.com',
            'role_id' => 2,
            'phone_number' => '+43123456789',
            'car_number' => 'CT-2026',
            'lat' => '48.205954',
            'long' => '16.372507',
        ])[0];
        $package = $this->package();

        $this->withHeaders(['Authorization' => 'Bearer ' . $customerToken])
            ->postJson('/api/v1/booking-store/v2', $this->bookingPayload($package->id))
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $order = Order::first();
        $this->assertSame(Order::STATUS_PROCESSING, $order->order_status);

        $this->artisan('assign:rider')->assertExitCode(0);

        $this->assertDatabaseHas('order_assigns', [
            'order_id' => $order->id,
            'rider_id' => $rider->id,
            'assign_status' => 'pending',
        ]);
        $this->assertSame(1, (int) $order->fresh()->is_assign);
    }

    /** @test */
    public function accepted_assignment_returns_driver_and_car_number_to_customer_booking_list()
    {
        [$customer, $customerToken] = $this->apiUser([
            'first_name' => 'Cust',
            'last_name' => 'Omer',
            'email' => 'customer@example.com',
            'role_id' => 3,
        ]);
        [$rider, $riderToken] = $this->apiUser([
            'first_name' => 'Demo',
            'last_name' => 'Driver',
            'email' => 'rider@example.com',
            'role_id' => 2,
            'phone_number' => '+43123456789',
            'car_number' => 'CT-2026',
            'lat' => '48.205954',
            'long' => '16.372507',
        ]);
        $package = $this->package();

        $this->withHeaders(['Authorization' => 'Bearer ' . $customerToken])
            ->postJson('/api/v1/booking-store/v2', $this->bookingPayload($package->id))
            ->assertStatus(200);

        $order = Order::first();
        $this->artisan('assign:rider')->assertExitCode(0);

        $this->withHeaders(['Authorization' => 'Bearer ' . $riderToken])
            ->postJson('/api/v1/auto-orders/response', [
                'order_id' => $order->id,
                'status' => 'accepted',
            ])
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $order->refresh();
        $this->assertSame($rider->id, (int) $order->rider_id);
        $this->assertSame(Order::STATUS_PICKING, $order->order_status);
        $this->assertDatabaseHas('order_statuses', [
            'order_id' => $order->id,
            'order_status' => Order::STATUS_PICKING,
        ]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $customerToken])
            ->getJson('/api/v1/bookings?type=upcoming')
            ->assertStatus(200)
            ->assertJsonPath('data.bookings.data.0.rider_first_name', 'Demo')
            ->assertJsonPath('data.bookings.data.0.rider_last_name', 'Driver')
            ->assertJsonPath('data.bookings.data.0.car_number', 'CT-2026')
            ->assertJsonPath('data.bookings.data.0.rider_id', $rider->id);
    }

    /** @test */
    public function customer_cannot_mark_order_delivered_without_local_simulator_flag()
    {
        [$customer, $customerToken] = $this->apiUser([
            'first_name' => 'Cust',
            'last_name' => 'Omer',
            'email' => 'customer@example.com',
            'role_id' => 3,
        ]);
        [$rider, $riderToken] = $this->apiUser([
            'first_name' => 'Demo',
            'last_name' => 'Driver',
            'email' => 'rider@example.com',
            'role_id' => 2,
            'phone_number' => '+43123456789',
            'car_number' => 'CT-2026',
            'lat' => '48.205954',
            'long' => '16.372507',
        ]);
        $package = $this->package();

        $this->withHeaders(['Authorization' => 'Bearer ' . $customerToken])
            ->postJson('/api/v1/booking-store/v2', $this->bookingPayload($package->id))
            ->assertStatus(200);

        $order = Order::first();
        $this->artisan('assign:rider')->assertExitCode(0);

        $this->withHeaders(['Authorization' => 'Bearer ' . $riderToken])
            ->postJson('/api/v1/auto-orders/response', [
                'order_id' => $order->id,
                'status' => 'accepted',
            ])
            ->assertStatus(200);

        $this->assertSame($rider->id, (int) $order->fresh()->rider_id);

        $this->withHeaders(['Authorization' => 'Bearer ' . $customerToken])
            ->putJson("/api/v1/bookings/{$order->id}/status", [
                'order_id' => $order->id,
                'order_status' => Order::STATUS_DELIVERED,
            ])
            ->assertStatus(403);

        $this->assertSame(Order::STATUS_PICKING, $order->fresh()->order_status);
    }

    /** @test */
    public function local_simulator_completion_can_mark_assigned_customer_order_delivered()
    {
        [$customer, $customerToken] = $this->apiUser([
            'first_name' => 'Cust',
            'last_name' => 'Omer',
            'email' => 'customer@example.com',
            'role_id' => 3,
        ]);
        [, $riderToken] = $this->apiUser([
            'first_name' => 'Demo',
            'last_name' => 'Driver',
            'email' => 'rider@example.com',
            'role_id' => 2,
            'phone_number' => '+43123456789',
            'car_number' => 'CT-2026',
            'lat' => '48.205954',
            'long' => '16.372507',
        ]);
        $package = $this->package();

        $this->withHeaders(['Authorization' => 'Bearer ' . $customerToken])
            ->postJson('/api/v1/booking-store/v2', $this->bookingPayload($package->id))
            ->assertStatus(200);

        $order = Order::first();
        $this->artisan('assign:rider')->assertExitCode(0);

        $this->withHeaders(['Authorization' => 'Bearer ' . $riderToken])
            ->postJson('/api/v1/auto-orders/response', [
                'order_id' => $order->id,
                'status' => 'accepted',
            ])
            ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $customerToken])
            ->putJson("/api/v1/bookings/{$order->id}/status", [
                'order_id' => $order->id,
                'order_status' => Order::STATUS_DELIVERED,
                'simulated_tracking' => true,
            ])
            ->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->order_status);
        $this->assertNotNull($order->fresh()->end_time);
    }

    private function apiUser(array $overrides): array
    {
        $user = User::forceCreate(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => Str::uuid() . '@example.com',
            'password' => Hash::make('password'),
            'role_id' => 3,
            'phone_number' => '5550000',
            'IsActive' => 1,
            'confirmed' => 1,
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
            'id' => $user->id,
        ], config('app.jwt_secret'), 'HS256');

        $user->access_token = $token;
        $user->save();

        return [$user, $token];
    }

    private function package(): Package
    {
        return Package::create([
            'name' => 'Standard',
            'weight' => 5,
            'unit' => 'kg',
            'per_km_charges' => 2.50,
            'fixed_price' => 10.00,
        ]);
    }

    private function bookingPayload(int $packageId): array
    {
        return [
            'picked_time' => now()->addHour()->toDateTimeString(),
            'package_id' => $packageId,
            'transaction_id' => 'tx_test_123',
            'payment_type' => 'cod',
            'poly_points' => 'enc_polyline',
            'with_helper' => false,
            'location' => json_encode([[
                'start_location' => 'Währinger Straße 20, Vienna, Austria',
                'end_location' => 'Mariahilfer Straße, Vienna, Austria',
                'start_lat' => 48.205954,
                'start_long' => 16.372507,
                'end_lat' => 48.2020422,
                'end_long' => 16.3611048,
            ]]),
            'total_meter' => 4000,
            'total_second' => 600,
            'total_amount' => 0.01,
            'response' => '{}',
        ];
    }
}
