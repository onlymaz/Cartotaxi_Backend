<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Utilities\FireBaseRealTimeDatabase;
use Illuminate\Support\Facades\DB;

class TestLiveTracking extends Command
{
    protected $signature = 'test:live-tracking {--scenario=all : Test scenario (all, movement, orders, offline, multiple)}';
    protected $description = 'Test live tracking functionality with realistic scenarios';

    public function handle()
    {
        $scenario = $this->option('scenario');

        $this->info('🚀 Starting Live Tracking Test...');
        $this->newLine();

        // Ensure we have riders
        $riders = User::where('role_id', 2)->get();
        
        if ($riders->count() < 3) {
            $this->error('❌ Need at least 3 riders for testing. Please seed riders first.');
            return 1;
        }

        $this->info("✅ Found {$riders->count()} riders");
        $this->newLine();

        switch ($scenario) {
            case 'movement':
                $this->testRiderMovement($riders);
                break;
            case 'orders':
                $this->testRidersWithOrders($riders);
                break;
            case 'offline':
                $this->testOfflineRiders($riders);
                break;
            case 'multiple':
                $this->testMultipleScenarios($riders);
                break;
            case 'all':
            default:
                $this->testAllScenarios($riders);
                break;
        }

        $this->newLine();
        $this->info('✅ Testing complete! Check the live map at /live/map');
        
        return 0;
    }

    private function testAllScenarios($riders)
    {
        $this->info('📋 Running All Test Scenarios...');
        $this->newLine();
        
        $this->testRiderMovement($riders->take(2));
        sleep(2);
        
        $this->testRidersWithOrders($riders->skip(2)->take(2));
        sleep(2);
        
        $this->testOfflineRiders($riders->skip(4)->take(1));
    }
    
    private function testRiderMovement($riders)
    {
        $this->info('🚗 Scenario 1: Rider Movement Simulation');
        $this->line('   Simulating riders moving along realistic routes...');
        
        // Vienna city center coordinates
        $startLat = 48.2082;
        $startLng = 16.3738;
        
        foreach ($riders as $index => $rider) {
            $this->line("   → Testing rider: {$rider->first_name} {$rider->last_name} (ID: {$rider->id})");
                
            // Simulate movement along a route (like driving through Vienna)
            $route = $this->generateRoute($startLat + ($index * 0.01), $startLng + ($index * 0.01));
            
            foreach ($route as $step => $location) {
                $this->updateRiderLocation($rider->id, $location['lat'], $location['lng']);
                $this->line("      Step " . ($step + 1) . ": {$location['lat']}, {$location['lng']}");
                
                if ($step < count($route) - 1) {
                    sleep(2); // Wait 2 seconds between updates
                }
            }
            
            $this->info("   ✅ Completed movement simulation for rider {$rider->id}");
        }
    }

    private function testRidersWithOrders($riders)
    {
        $this->info('📦 Scenario 2: Riders with Active Orders');
        $this->line('   Simulating riders delivering orders...');
        
        foreach ($riders as $index => $rider) {
            // Assign an active order if available
            $order = Order::where('rider_id', $rider->id)
                ->whereNotIn('order_status', ['delivered', 'cancel'])
                ->first();
            
            if (!$order) {
                // Create a test order
                $order = Order::create([
                    'customer_id' => User::where('role_id', 3)->first()->id ?? 1,
                    'rider_id' => $rider->id,
                    'booking_id' => 'TEST' . rand(1000, 9999),
                    'order_status' => 'on_way',
                    'start_location' => 'Vienna City Center',
                    'end_location' => 'Vienna Airport',
                    'total_amount' => rand(50, 200),
                    'created_at' => now(),
                ]);
            }
            
            $this->line("   → Rider {$rider->first_name} has active order: {$order->booking_id}");
            
            // Simulate movement from pickup to delivery
            $pickup = ['lat' => 48.2082, 'lng' => 16.3738]; // City center
            $delivery = ['lat' => 48.1103, 'lng' => 16.5697]; // Airport
            
            $route = $this->interpolateRoute($pickup, $delivery, 10);
            
            foreach ($route as $step => $location) {
                $this->updateRiderLocation($rider->id, $location['lat'], $location['lng']);
                $this->line("      Moving to delivery: Step " . ($step + 1) . "/" . count($route));
                sleep(2);
            }
            
            $this->info("   ✅ Rider {$rider->id} completed delivery simulation");
        }
    }

    private function testOfflineRiders($riders)
    {
        $this->info('📴 Scenario 3: Offline Riders');
        $this->line('   Simulating riders going offline...');
        
        foreach ($riders as $rider) {
            $this->line("   → Making rider {$rider->first_name} offline");
            // Clear location
            $this->updateRiderLocation($rider->id, null, null);
            $this->info("   ✅ Rider {$rider->id} is now offline");
        }
    }
    
    private function testMultipleScenarios($riders)
    {
        $this->info('🔄 Scenario 4: Multiple Concurrent Scenarios');
        $this->line('   Simulating multiple riders in different states...');
        
        $scenarios = [
            ['rider' => $riders[0] ?? null, 'type' => 'available', 'route' => 'city_center'],
            ['rider' => $riders[1] ?? null, 'type' => 'on_order', 'route' => 'airport'],
            ['rider' => $riders[2] ?? null, 'type' => 'offline', 'route' => null],
        ];
        
        foreach ($scenarios as $scenario) {
            if (!$scenario['rider']) continue;
            
            $rider = $scenario['rider'];
            $this->line("   → {$rider->first_name}: {$scenario['type']}");
            
            if ($scenario['type'] === 'offline') {
                $this->updateRiderLocation($rider->id, null, null);
            } else {
                $route = $scenario['route'] === 'city_center' 
                    ? $this->generateCityRoute()
                    : $this->generateAirportRoute();
                
                foreach ($route as $location) {
                    $this->updateRiderLocation($rider->id, $location['lat'], $location['lng']);
                    sleep(1);
                }
            }
        }
    }

    private function generateRoute($startLat, $startLng, $steps = 15)
    {
        $route = [];
        $endLat = $startLat + 0.05; // Move north
        $endLng = $startLng + 0.05; // Move east
        
        for ($i = 0; $i <= $steps; $i++) {
            $progress = $i / $steps;
            $route[] = [
                'lat' => $startLat + ($endLat - $startLat) * $progress + (rand(-100, 100) / 10000), // Add slight randomness
                'lng' => $startLng + ($endLng - $startLng) * $progress + (rand(-100, 100) / 10000),
            ];
        }
        
        return $route;
    }
    
    private function generateCityRoute()
    {
        // Route through Vienna city center
        return [
            ['lat' => 48.2082, 'lng' => 16.3738], // Stephansplatz
            ['lat' => 48.2100, 'lng' => 16.3750], // Moving north
            ['lat' => 48.2120, 'lng' => 16.3770],
            ['lat' => 48.2140, 'lng' => 16.3790],
            ['lat' => 48.2160, 'lng' => 16.3810],
        ];
    }
    
    private function generateAirportRoute()
    {
        // Route from city to airport
        return [
            ['lat' => 48.2082, 'lng' => 16.3738], // City center
            ['lat' => 48.1950, 'lng' => 16.4000],
            ['lat' => 48.1800, 'lng' => 16.4500],
            ['lat' => 48.1500, 'lng' => 16.5200],
            ['lat' => 48.1103, 'lng' => 16.5697], // Airport
        ];
    }

    private function interpolateRoute($start, $end, $steps = 10)
    {
        $route = [];
        for ($i = 0; $i <= $steps; $i++) {
            $progress = $i / $steps;
            $route[] = [
                'lat' => $start['lat'] + ($end['lat'] - $start['lat']) * $progress,
                'lng' => $start['lng'] + ($end['lng'] - $start['lng']) * $progress,
            ];
        }
        return $route;
    }

    private function updateRiderLocation($riderId, $lat, $lng)
    {
        try {
            // Update database
            $rider = User::find($riderId);
            if ($rider) {
                $rider->lat = $lat;
                $rider->long = $lng;
            $rider->save();
            }
            
            // Update Firebase (with error handling)
            try {
                if ($lat !== null && $lng !== null) {
                    $data = [
                        'lat' => (string)$lat,
                        'long' => (string)$lng,
                        'role_id' => 2,
                        'email' => $rider->email ?? '',
                        'first_name' => $rider->first_name ?? '',
                        'last_name' => $rider->last_name ?? '',
                    ];
                    
                    $reference = 'users/' . $riderId;
                    FireBaseRealTimeDatabase::StoreData($reference, $data);
                } else {
                    // Clear location in Firebase
                    $reference = 'users/' . $riderId;
                    FireBaseRealTimeDatabase::StoreData($reference, ['lat' => '', 'long' => '']);
                }
            } catch (\Exception $e) {
                // Firebase not configured - that's okay, database updates still work
                // AJAX polling will pick up the changes
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error updating rider {$riderId}: " . $e->getMessage());
        }
    }
}
