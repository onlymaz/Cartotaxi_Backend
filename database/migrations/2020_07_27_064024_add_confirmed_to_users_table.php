<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfirmedToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('device')->nullable()->after('long');
            $table->string('provider')->nullable()->after('long');
            $table->string('confirmation_code')->nullable()->after('provider');
            $table->boolean('confirmed')->default(0)->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('device');
            $table->dropColumn('provider');
            $table->dropColumn('confirmation_code');
            $table->dropColumn('confirmed');
        });
    }
}
