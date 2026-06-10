<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns order_assigns into a full dispatch audit log: which rider was
 * offered the ride, in what order, how far away they were, and how/when
 * they responded (accepted / rejected / timed out / superseded).
 */
class AddDispatchLogFieldsToOrderAssigns extends Migration
{
    public function up()
    {
        Schema::table('order_assigns', function (Blueprint $table) {
            $table->unsignedInteger('attempt')->default(1)->after('rider_id');
            $table->decimal('distance_km', 8, 2)->nullable()->after('attempt');
            $table->timestamp('responded_at')->nullable()->after('assign_status');
            $table->string('note')->nullable()->after('responded_at');
            $table->index(['order_id', 'assign_status']);
        });
    }

    public function down()
    {
        Schema::table('order_assigns', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'assign_status']);
            $table->dropColumn(['attempt', 'distance_km', 'responded_at', 'note']);
        });
    }
}
