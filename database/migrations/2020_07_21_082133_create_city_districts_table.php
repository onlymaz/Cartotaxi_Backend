<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city_districts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('city_id')->default(0);
            $table->string('district_name')->nullable();
            $table->text('polygons')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('city_districts');
    }
}
