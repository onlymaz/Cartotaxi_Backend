<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_names     =   ['admin','rider','customer'];
        foreach ($role_names as $role_name)
        {
            $role   =   Role::where('name','=',$role_name)->first();
            if ($role){
                $role->update([
                    'name'   =>   $role_name
                ]);
            }else{
                Role::create([
                    'name'   =>   $role_name
                ]);
            }
        }
    }
}
