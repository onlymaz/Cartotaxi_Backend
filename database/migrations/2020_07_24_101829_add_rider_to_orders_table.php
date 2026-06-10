<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRiderToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('rider_id')->default(0)->after('customer_id');
            $table->bigInteger('start_district_id')->default(0)->after('rider_id');
            $table->bigInteger('end_district_id')->default(0)->after('start_district_id');
            $table->double('fixed_price')->default(0)->after('description');
            $table->double('per_km_charges')->default(0)->after('fixed_price');
            $table->double('total_amount')->default(0)->after('fixed_price');
            $table->double('total_meter')->default(0)->after('total_amount');
            $table->double('total_second')->default(0)->after('total_meter');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('rider_id');
            $table->dropColumn('start_district_id');
            $table->dropColumn('end_district_id');
            $table->dropColumn('fixed_price');
            $table->dropColumn('per_km_charges');
            $table->dropColumn('total_amount');
            $table->dropColumn('total_meter');
            $table->dropColumn('total_second');
        });
    }
}
