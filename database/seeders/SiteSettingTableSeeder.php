<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;
class SiteSettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $site_setting   =   SiteSetting::find(1);
        if (!$site_setting){
            SiteSetting::create([
                'provider_accept_timeout'   =>  0,
                'provider_search_radius'   =>  0,
                'social_login'   =>  1,
            ]);
        }
    }
}
