<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(RoleTableSeeder::class);
         $this->call(UserTableSeeder::class);
         $this->call(CityDistrictTableSeeder::class);
         $this->call(ServiceZonePolygonSeeder::class);
         $this->call(ReviewTypeTableSeeder::class);
         $this->call(SiteSettingTableSeeder::class);
         $this->call(GatewayTableSeeder::class);
    }
}
