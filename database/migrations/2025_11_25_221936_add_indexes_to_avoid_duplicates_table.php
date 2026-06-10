<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToAvoidDuplicatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avoid_duplicates', function (Blueprint $table) {
            $table->unique(['order_id', 'rider_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avoid_duplicates', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'rider_id']);
        });
    }
}
