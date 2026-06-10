<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnumToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('order_status',['pending','processing','picking','picked_up','on_way','accident','not_received','refused','delivered','cancel'])->default('pending');
            });
        } else {
            // SQLite has no native ENUM. The earlier migration
            // 2020_07_30_082811_change_string_to_orders_table drops the
            // order_status column unconditionally; without this branch the
            // orders table ends up with NO order_status under SQLite, which
            // broke the entire test suite. Use a plain string with the same
            // default — application-level validation pins the allowed values.
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'order_status')) {
                    $table->string('order_status')->default('pending');
                }
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_status');
        });
    }
}
