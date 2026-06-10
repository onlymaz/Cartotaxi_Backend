<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Utilities\FireBaseRealTimeDatabase;

class SimulateRiderMovement extends Command
{
    protected $signature = 'simulate:rider-movement {rider_id} {--duration=60 : Duration in seconds} {--speed=50 : Speed in km/h}';
    protected $description = 'Simulate realistic rider movement for testing';

    public function handle()
    {
        $riderId = $this->argument('rider_id');
        $duration = (int)$this->option('duration');
        $speed = (int)$this->option('speed'); // km/h

        $rider = User::find($riderId);
        
        if (!$rider || $rider->role_id != 2) {
            $this->error("❌ Rider with ID {$riderId} not found or not a rider");
            return 1;
        }

        $this->info("🚗 Simulating movement for: {$rider->first_name} {$rider->last_name}");
        $this->info("   Duration: {$duration} seconds");
        $this->info("   Speed: {$speed} km/h");
        $this->newLine();

        // Start from current location or default
        $currentLat = $rider->lat ? (float)$rider->lat : 48.2082;
        $currentLng = $rider->long ? (float)$rider->long : 16.3738;
        
        $startTime = time();
        $updateInterval = 3; // Update every 3 seconds
        $distancePerUpdate = ($speed / 3600) * $updateInterval; // km per update
        
        $this->info("📍 Starting location: {$currentLat}, {$currentLng}");
        $this->info("   Updates every {$updateInterval} seconds");
        $this->newLine();
        
        $step = 0;
        while ((time() - $startTime) < $duration) {
            $step++;
            
            // Calculate new position (moving in a random direction)
            $bearing = rand(0, 360); // Random direction
            $newLocation = $this->calculateDestination($currentLat, $currentLng, $distancePerUpdate, $bearing);
            
            $currentLat = $newLocation['lat'];
            $currentLng = $newLocation['lng'];

            // Update location
            $this->updateLocation($riderId, $currentLat, $currentLng);
            
            $this->line("   Step {$step}: {$currentLat}, {$currentLng} (Bearing: {$bearing}°)");
            
            sleep($updateInterval);
        }

        $this->newLine();
        $this->info("✅ Simulation complete! {$step} location updates sent");
        
        return 0;
    }

    private function calculateDestination($lat, $lng, $distanceKm, $bearing)
    {
        $earthRadius = 6371; // km
        
        $lat1 = deg2rad($lat);
        $lng1 = deg2rad($lng);
        $bearingRad = deg2rad($bearing);
        
        $lat2 = asin(sin($lat1) * cos($distanceKm / $earthRadius) +
                     cos($lat1) * sin($distanceKm / $earthRadius) * cos($bearingRad));
        
        $lng2 = $lng1 + atan2(sin($bearingRad) * sin($distanceKm / $earthRadius) * cos($lat1),
                              cos($distanceKm / $earthRadius) - sin($lat1) * sin($lat2));
        
        return [
            'lat' => rad2deg($lat2),
            'lng' => rad2deg($lng2)
        ];
    }
    
    private function updateLocation($riderId, $lat, $lng)
    {
        try {
            // Update database
            $rider = User::find($riderId);
            $rider->lat = (string)$lat;
            $rider->long = (string)$lng;
            $rider->save();

            // Update Firebase
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
            $this->error("   ❌ Error: " . $e->getMessage());
        }
    }
}
