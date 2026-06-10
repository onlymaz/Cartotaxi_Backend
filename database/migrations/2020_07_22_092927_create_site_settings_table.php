<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('site_icon')->nullable();
            $table->string('playstore_link')->nullable();
            $table->string('appstore_link')->nullable();
            $table->integer('provider_accept_timeout')->default(0);
            $table->integer('provider_search_radius')->default(0);
            $table->string('sos_number')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('help_content')->nullable();
            $table->string('google_map_key')->nullable();
            $table->boolean('social_login')->default(1);
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
        Schema::dropIfExists('site_settings');
    }
}
