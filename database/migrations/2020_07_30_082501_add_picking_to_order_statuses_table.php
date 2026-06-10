<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class AddPickingToOrderStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('order_statuses', function (Blueprint $table) {
                DB::statement("ALTER TABLE `order_statuses` CHANGE `order_status` `order_status` ENUM('pending','processing','picking','picked_up','on_way','accident','not_received','refused','delivered','cancel');");
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_statuses', function (Blueprint $table) {
            DB::statement("ALTER TABLE `order_statuses` CHANGE `order_status` `order_status` ENUM('pending','processing','picked_up','on_way','accident','not_received','refused','delivered','cancel');");
        });
    }
}
