<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderSubTrip;
use App\Utilities\FireBaseRealTimeDatabase;

class SimulateDeliveryRoute extends Command
{
    protected $signature = 'simulate:delivery-route 
                            {rider_id : The ID of the rider to simulate}
                            {--order_id= : Specific order ID to use (optional)}
                            {--speed=50 : Speed in km/h (default: 50)}
                            {--update-interval=3 : Update interval in seconds (default: 3)}';
    
    protected $description = 'Simulate a complete delivery route - rider moves from pickup to dropoff location';

    public function handle()
    {
        $riderId = $this->argument('rider_id');
        $speed = (float)$this->option('speed'); // km/h
        $updateInterval = (int)$this->option('update-interval');
        $orderId = $this->option('order_id');
        
        $rider = User::find($riderId);
        
        if (!$rider || $rider->role_id != 2) {
            $this->error("❌ Rider with ID {$riderId} not found or not a rider");
            return 1;
        }
        
        // Get or create an active order for this rider
        $order = null;
        if ($orderId) {
            $order = Order::find($orderId);
            if (!$order || $order->rider_id != $riderId) {
                $this->error("❌ Order {$orderId} not found or not assigned to this rider");
                return 1;
            }
        } else {
            // Find an active order for this rider
            $order = Order::where('rider_id', $riderId)
                ->whereNotIn('order_status', ['delivered', 'cancel', 'refused'])
                ->first();
        }
        
        // If no order, create a test order with coordinates
        if (!$order) {
            $this->warn("⚠️  No active order found. Creating a test order...");
            
            $customer = \App\Models\User::where('role_id', 3)->first();
            if (!$customer) {
                $this->error("❌ No customer found. Please create a customer first.");
                return 1;
            }
            
            $order = Order::create([
                'customer_id' => $customer->id,
                'rider_id' => $riderId,
                'booking_id' => 'TEST' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'order_status' => 'on_way',
                'start_location' => 'Vienna City Center',
                'end_location' => 'Vienna Airport',
                'total_amount' => 100,
                'created_at' => now(),
            ]);
            
            // Create OrderSubTrip with coordinates
            OrderSubTrip::create([
                'order_id' => $order->id,
                'start_district_id' => 1,
                'end_district_id' => 1,
                'start_location' => 'Vienna City Center',
                'end_location' => 'Vienna Airport',
                'start_lat' => '48.2082',
                'start_long' => '16.3738',
                'end_lat' => '48.1103',
                'end_long' => '16.5697',
                'total_amount' => 100,
                'total_meter' => 20000,
                'total_second' => 1800,
            ]);
            
            $this->info("✅ Created test order: {$order->booking_id}");
        }
        
        // Get route coordinates
        $subTrip = OrderSubTrip::where('order_id', $order->id)->first();
        
        if (!$subTrip || !$subTrip->start_lat || !$subTrip->start_long || !$subTrip->end_lat || !$subTrip->end_long) {
            $this->error("❌ Order does not have valid route coordinates. Please run: php artisan orders:add-coordinates");
            return 1;
        }
        
        $startLat = (float)$subTrip->start_lat;
        $startLng = (float)$subTrip->start_long;
        $endLat = (float)$subTrip->end_lat;
        $endLng = (float)$subTrip->end_long;
        
        // Get rider's current position or use start location
        $currentLat = $rider->lat ? (float)$rider->lat : $startLat;
        $currentLng = $rider->long ? (float)$rider->long : $startLng;
        
        $this->info("🚗 Simulating Delivery Route");
        $this->info("   Rider: {$rider->first_name} {$rider->last_name} (ID: {$rider->id})");
        $this->info("   Order: {$order->booking_id}");
        $this->info("   Speed: {$speed} km/h");
        $this->info("   Update Interval: {$updateInterval} seconds");
        $this->newLine();
        
        $this->info("📍 Route Details:");
        $this->info("   Pickup:  {$startLat}, {$startLng} ({$order->start_location})");
        $this->info("   Dropoff: {$endLat}, {$endLng} ({$order->end_location})");
        $this->info("   Current: {$currentLat}, {$currentLng}");
        $this->newLine();
        
        // Calculate total distance
        $totalDistance = $this->calculateDistance($currentLat, $currentLng, $endLat, $endLng);
        $this->info("   Total Distance: " . number_format($totalDistance, 2) . " km");
        
        // Calculate estimated time
        $estimatedTime = ($totalDistance / $speed) * 3600; // seconds
        $this->info("   Estimated Time: " . gmdate("H:i:s", (int)$estimatedTime));
        $this->newLine();
        
        // Generate route points
        $routePoints = $this->generateRoutePoints($currentLat, $currentLng, $endLat, $endLng, $speed, $updateInterval);
        $totalSteps = count($routePoints);
        
        $this->info("   Generated {$totalSteps} route points");
        $this->info("   Starting simulation in 2 seconds...");
        $this->newLine();
        
        sleep(2);
        
        // Start simulation
        $progressBar = $this->output->createProgressBar($totalSteps);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Moving...');
        $progressBar->start();
        
        foreach ($routePoints as $index => $point) {
            $this->updateLocation($rider->id, $point['lat'], $point['lng']);
            
            $distanceRemaining = $this->calculateDistance($point['lat'], $point['lng'], $endLat, $endLng);
            $progress = (($index + 1) / $totalSteps) * 100;
            
            $progressBar->setMessage(sprintf(
                'Lat: %.6f, Lng: %.6f | Distance: %.2f km | Progress: %.1f%%',
                $point['lat'],
                $point['lng'],
                $distanceRemaining,
                $progress
            ));
            $progressBar->advance();
            
            if ($index < $totalSteps - 1) {
                sleep($updateInterval);
            }
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Simulation Complete!");
        $this->info("   Rider has reached the dropoff location");
        $this->newLine();
        $this->info("💡 Tip: Open http://localhost:9000/live/map to see the tracking in real-time");
        
        return 0;
    }
    
    /**
     * Generate route points from start to end
     */
    private function generateRoutePoints($startLat, $startLng, $endLat, $endLng, $speed, $interval)
    {
        $points = [];
        
        // Calculate total distance
        $totalDistance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng); // km
        
        // Calculate distance per update (speed in km/h, interval in seconds)
        $distancePerUpdate = ($speed / 3600) * $interval; // km per update
        
        // Calculate number of steps
        $numSteps = max(10, (int)ceil($totalDistance / $distancePerUpdate));
        
        // Generate intermediate points
        for ($i = 0; $i <= $numSteps; $i++) {
            $ratio = $i / $numSteps;
            
            // Use linear interpolation with slight curve for realism
            $lat = $startLat + ($endLat - $startLat) * $ratio;
            $lng = $startLng + ($endLng - $startLng) * $ratio;
            
            // Add slight random variation to simulate real road movement
            $lat += (rand(-100, 100) / 1000000); // ~10 meters variation
            $lng += (rand(-100, 100) / 1000000);
            
            $points[] = [
                'lat' => $lat,
                'lng' => $lng,
                'step' => $i,
                'total' => $numSteps
            ];
        }
        
        return $points;
    }
    
    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Update rider location in database and Firebase
     */
    private function updateLocation($riderId, $lat, $lng)
    {
        try {
            // Update database
            $rider = User::find($riderId);
            if ($rider) {
                $rider->lat = (string)$lat;
                $rider->long = (string)$lng;
                $rider->save();
            }
            
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
                
                $reference = 'users/' . $riderId;
                FireBaseRealTimeDatabase::StoreData($reference, $data);
            } catch (\Exception $e) {
                // Firebase not configured - that's okay, database updates still work
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error updating location: " . $e->getMessage());
        }
    }
}

