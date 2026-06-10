<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusBookingToOrderSubTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_sub_trips', function (Blueprint $table) {
            $table->string('rider_status')->nullable()->after('paths');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_sub_trips', function (Blueprint $table) {
            $table->dropColumn('rider_status');
        });
    }
}
