<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewType;
class ReviewTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types  = [
            'rider',
            'customer'];

        foreach ($types as $type)
        {
            $review_type    =   ReviewType::where('name',$type)->first();
            if(!$review_type){
                ReviewType::create([
                    'name' =>  $type,
                    'description' =>  $type,
                    'types' =>  $type,
                ]);
            }
        }
    }
}
