<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\City;
use App\Models\CityDistrict;
class CityDistrictTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $country    =   Country::where('country_name','Austria')->first();
        if (!$country){
            $country    =   Country::create([
                'country_name'  =>  'Austria'
            ]);
            $city   =   City::where('city_name','Vienna')
                            ->where('country_id',$country->id)->first();
            if (!$city){
                $city   =   City::create([
                     'country_id'    =>  $country->id,
                     'city_name'    =>  'Vienna',
                ]);

                $districts  =   ['Austria','Vienna'];

                foreach ($districts as $district){
                    $district_exist =   CityDistrict::where('city_id',$city->id)
                                                    ->where('district_name',$district)->first();
                    if (!$district_exist){
                        CityDistrict::create([
                            'city_id'   =>   $city->id,
                            'district_name'   =>   $district,
                            'IsActive'   =>   0,
                            'polygons'   =>   '',
                        ]);
                    }
                }
            }
        }
    }
}
