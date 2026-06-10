<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gateway;
class GatewayTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array  =   ['cod','Paypal'];
        foreach ($array as $value)
        {
            $gateway    =   Gateway::where('name',$value)->first();
            if (!$gateway){
                Gateway::create([
                    'name'  =>  $value,
                    'description'   =>  '',
                    'fields'        =>  '',
                    'isActive'      =>  1
                ]);
            }
        }
    }
}
