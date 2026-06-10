<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderSubTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_sub_trips', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->default(0);
            $table->bigInteger('start_district_id')->default(0);
            $table->bigInteger('end_district_id')->default(0);
            $table->string('start_location')->nullable();
            $table->string('end_location')->nullable();
            $table->string('start_lat')->nullable();
            $table->string('start_long')->nullable();
            $table->string('end_lat')->nullable();
            $table->string('end_long')->nullable();
            $table->double('total_amount')->default(0);
            $table->double('total_meter')->default(0);
            $table->double('total_second')->default(0);
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
        Schema::dropIfExists('order_sub_trips');
    }
}
