<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use App\Models\Package;
use App\Models\Gateway;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get or create customers (role_id = 3)
        $customers = User::where('role_id', 3)->get();
        if ($customers->isEmpty()) {
            $customers = collect();
            for ($i = 1; $i <= 5; $i++) {
                $customers->push(User::create([
                    'first_name' => 'Customer',
                    'last_name' => $i,
                    'email' => 'customer' . $i . '@test.com',
                    'password' => bcrypt('password'),
                    'role_id' => 3,
                    'phone_number' => '+123456789' . $i,
                    'confirmed' => 1
                ]));
            }
        }

        // Get or create riders (role_id = 2)
        $riders = User::where('role_id', 2)->get();
        if ($riders->isEmpty()) {
            $riders = collect();
            for ($i = 1; $i <= 3; $i++) {
                $riders->push(User::create([
                    'first_name' => 'Rider',
                    'last_name' => $i,
                    'email' => 'rider' . $i . '@test.com',
                'password' => bcrypt('password'),
                    'role_id' => 2,
                    'phone_number' => '+123456780' . $i,
                    'confirmed' => 1
                ]));
            }
        }

        // Get or create a package
        $package = Package::first();
        if (!$package) {
            $package = Package::create([
                'name' => 'Standard Package',
                'weight' => 10,
                'unit' => 'kg',
                'fixed_price' => 20,
                'per_km_charges' => 2
            ]);
        }

        // Get or create gateways
        $gateways = Gateway::all();
        if ($gateways->isEmpty()) {
            $gateways = collect();
            $gateways->push(Gateway::create(['name' => 'Cash on Delivery']));
            $gateways->push(Gateway::create(['name' => 'Stripe']));
            $gateways->push(Gateway::create(['name' => 'PayPal']));
        }

        // Create orders with different statuses
        $statuses = [
            'pending' => 8,
            'processing' => 6,
            'picking' => 4,
            'picked_up' => 3,
            'on_way' => 5,
            'delivered' => 12,
            'cancel' => 3,
            'accident' => 1,
            'refused' => 2
        ];

        $locations = [
            ['start' => 'Vienna Central Station, Austria', 'end' => 'Schönbrunn Palace, Vienna'],
            ['start' => 'Salzburg Old Town, Austria', 'end' => 'Mirabell Palace, Salzburg'],
            ['start' => 'Innsbruck City Center, Austria', 'end' => 'Nordkette Cable Car, Innsbruck'],
            ['start' => 'Graz Main Square, Austria', 'end' => 'Graz Clock Tower, Graz'],
            ['start' => 'Linz Hauptplatz, Austria', 'end' => 'Pöstlingberg, Linz'],
            ['start' => 'Klagenfurt City Center, Austria', 'end' => 'Wörthersee, Klagenfurt'],
            ['start' => 'Villach City, Austria', 'end' => 'Dobratsch Nature Park, Villach'],
            ['start' => 'Wels City Center, Austria', 'end' => 'Wels Castle, Wels'],
        ];

        $transactionTypes = ['viaCod', 'viaStripe', 'viaPaypal', 'viaWeekly'];
        $paymentStatuses = ['pending', 'completed'];

        $orderCount = 0;
        foreach ($statuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $customer = $customers->random();
                $location = $locations[array_rand($locations)];
                $pickedTime = Carbon::now()->subDays(rand(0, 60))->subHours(rand(0, 23));
                
                // Assign rider for non-pending orders (use 0 if no rider)
                $riderId = ($status !== 'pending') ? $riders->random()->id : 0;
                
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'rider_id' => $riderId,
                    'package_id' => $package->id,
                'booking_id' => Order::CreateRandomBookingID(),
                    'start_location' => $location['start'],
                    'end_location' => $location['end'],
                    'picked_time' => $pickedTime->format('Y-m-d H:i:s'),
                    'order_status' => $status,
                    'total_amount' => rand(25, 150),
                    'total_meter' => rand(5000, 50000), // 5km to 50km
                    'total_second' => rand(600, 3600), // 10 min to 1 hour
                    'fixed_price' => $package->fixed_price,
                    'per_km_charges' => $package->per_km_charges,
                    'start_district_id' => 1,
                    'end_district_id' => 1,
                    'description' => 'Dummy order for testing purposes - Order #' . ($orderCount + 1),
                    'name' => '',
                    'start_time' => ($status !== 'pending' && $status !== 'processing') ? $pickedTime->addHours(rand(1, 3)) : null,
                    'end_time' => ($status === 'delivered') ? $pickedTime->addHours(rand(2, 5)) : null,
                    'created_at' => $pickedTime->subDays(rand(0, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 5))
                ]);

                // Create payment for each order
                $gateway = $gateways->random();
                $paymentStatus = ($status === 'delivered') ? 'completed' : $paymentStatuses[array_rand($paymentStatuses)];
                
                Payment::create([
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'gateway_id' => $gateway->id,
                    'amount' => $order->total_amount,
                    'transactions' => $transactionTypes[array_rand($transactionTypes)],
                    'status' => $paymentStatus,
                    'response' => '',
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at
                ]);

                $orderCount++;
            }
        }

        echo "Created {$orderCount} dummy orders with various statuses!\n";
    }
}
