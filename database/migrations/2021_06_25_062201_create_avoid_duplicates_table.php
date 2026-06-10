<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvoidDuplicatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avoid_duplicates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->default(0);
            $table->bigInteger('rider_id')->default(0);
            $table->bigInteger('rating_count')->default(0);
            $table->date('start_week')->nullable();
            $table->date('end_week')->nullable();
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
        Schema::dropIfExists('avoid_duplicates');
    }
}
