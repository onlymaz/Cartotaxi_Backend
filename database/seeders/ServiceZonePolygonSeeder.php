<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CityDistrict;

/**
 * Fills the two service zones (Vienna + Lower Austria) with their real
 * administrative boundaries from OpenStreetMap (database/data/zone_*.json,
 * format: JSON array of {lat, lng} points — what the booking map expects).
 *
 * The dispatcher form hardcodes the Cargo Taxi stand as district 2, so
 * district 2 must be Vienna. Lower Austria uses its outer ring only (no
 * Vienna-enclave hole); points inside Vienna still resolve to district 2
 * because the map JS checks polygons in district order and the last match
 * wins. Without polygons every booking address is rejected as "outside
 * the service area", so run this after CityDistrictTableSeeder.
 */
class ServiceZonePolygonSeeder extends Seeder
{
    public function run()
    {
        $zones = [
            1 => ['district_name' => 'Lower Austria', 'file' => 'zone_lower_austria.json'],
            2 => ['district_name' => 'Vienna',        'file' => 'zone_vienna.json'],
        ];

        foreach ($zones as $id => $zone) {
            $polygons = file_get_contents(database_path('data/'.$zone['file']));

            CityDistrict::where('id', $id)->update([
                'district_name' => $zone['district_name'],
                'polygons'      => $polygons,
                'IsActive'      => 1,
            ]);
        }
    }
}
