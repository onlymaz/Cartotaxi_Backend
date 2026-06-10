<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user   =   User::find(1);
        if (!$user){
            User::create([
                'first_name' => 'admin',
                'last_name' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make(12345678),
                'role_id'  => 1
            ]);
        }
    }
}
