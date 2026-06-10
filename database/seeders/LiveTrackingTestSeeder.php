<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Utilities\FireBaseRealTimeDatabase;

class LiveTrackingTestSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🧪 Setting up Live Tracking Test Data...');
        
        // Get or create test riders
        $riders = [];
        for ($i = 1; $i <= 5; $i++) {
            $rider = User::firstOrCreate(
                ['email' => "test_rider_{$i}@cargotaxi.test"],
                [
                    'first_name' => "Test",
                    'last_name' => "Rider {$i}",
                    'password' => bcrypt('password'),
                    'role_id' => 2,
                    'confirmed' => 1,
                    'IsActive' => 1,
                    'phone_number' => '+123456789' . $i,
                ]
            );
            $riders[] = $rider;
        }
        
        $this->command->info("✅ Created/Found " . count($riders) . " test riders");
        
        // Set initial locations for riders (Vienna area)
        $locations = [
            ['lat' => 48.2082, 'lng' => 16.3738], // City Center
            ['lat' => 48.2200, 'lng' => 16.3600], // North
            ['lat' => 48.1950, 'lng' => 16.3900], // South
            ['lat' => 48.2100, 'lng' => 16.4000], // East
            ['lat' => 48.2050, 'lng' => 16.3500], // West
        ];
        
        foreach ($riders as $index => $rider) {
            $location = $locations[$index] ?? $locations[0];
            $this->updateRiderLocation($rider, $location['lat'], $location['lng']);
            $this->command->info("   → Rider {$rider->id}: {$location['lat']}, {$location['lng']}");
        }
        
        // Create some test orders
        $customer = User::where('role_id', 3)->first();
        if (!$customer) {
            $customer = User::firstOrCreate(
                ['email' => 'test_customer@cargotaxi.test'],
                [
                    'first_name' => 'Test',
                    'last_name' => 'Customer',
                    'password' => bcrypt('password'),
                    'role_id' => 3,
                    'confirmed' => 1,
                ]
            );
        }
        
        // Assign orders to some riders
        $orderStatuses = ['pending', 'processing', 'picking', 'on_way'];
        foreach (array_slice($riders, 0, 3) as $index => $rider) {
            $order = Order::create([
                'customer_id' => $customer->id,
                'rider_id' => $rider->id,
                'booking_id' => 'TEST' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'order_status' => $orderStatuses[$index] ?? 'on_way',
                'start_location' => 'Vienna City Center',
                'end_location' => 'Vienna Airport',
                'total_amount' => rand(50, 200),
                'created_at' => now(),
            ]);
            
            $this->command->info("   → Created order {$order->booking_id} for rider {$rider->id}");
        }
        
        $this->command->info('✅ Test data setup complete!');
        $this->command->info('');
        $this->command->info('📋 Test Scenarios Available:');
        $this->command->info('   1. php artisan test:live-tracking --scenario=movement');
        $this->command->info('   2. php artisan test:live-tracking --scenario=orders');
        $this->command->info('   3. php artisan test:live-tracking --scenario=offline');
        $this->command->info('   4. php artisan test:live-tracking --scenario=multiple');
        $this->command->info('   5. php artisan test:live-tracking --scenario=all');
        $this->command->info('');
        $this->command->info('🚗 Simulate Individual Rider:');
        $this->command->info('   php artisan simulate:rider-movement {rider_id} --duration=60 --speed=50');
    }
    
    private function updateRiderLocation($rider, $lat, $lng)
    {
        $rider->lat = (string)$lat;
        $rider->long = (string)$lng;
        $rider->save();
        
        // Update Firebase
        try {
            $data = [
                'lat' => (string)$lat,
                'long' => (string)$lng,
                'role_id' => 2,
                'email' => $rider->email ?? '',
                'first_name' => $rider->first_name ?? '',
                'last_name' => $rider->last_name ?? '',
            ];
            
            $reference = 'users/' . $rider->id;
            FireBaseRealTimeDatabase::StoreData($reference, $data);
        } catch (\Exception $e) {
            // Ignore Firebase errors during seeding
        }
    }
}

