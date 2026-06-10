<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Security + performance hardening rolled into a single migration to keep the
 * production-readiness change set traceable.
 *
 *   1. Add expiry timestamps for `forget_code` and `confirmation_code`. The
 *      Auth/ResetPassword flow now hashes both codes and rejects them after
 *      a short TTL — neither column existed before.
 *
 *   2. Add a unique index on `orders.booking_id` so the booking-ID generator's
 *      duplicate check is enforced by the database, not just by application
 *      logic. Also adds non-unique indexes on the columns hammered by the
 *      bookings list, dispatcher view, and rider lookups.
 *
 * The migration is idempotent: every Schema::hasColumn / hasIndex check is
 * wrapped so re-running it on a partially-migrated DB is a no-op.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'forget_code_expires_at')) {
                $table->timestamp('forget_code_expires_at')->nullable()->after('forget_code');
            }
            if (!Schema::hasColumn('users', 'confirmation_code_expires_at')) {
                $table->timestamp('confirmation_code_expires_at')->nullable()->after('confirmation_code');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            // Unique on booking_id — backs the application-level retry loop in
            // Order::CreateRandomBookingID(). Without this, the loop's duplicate
            // check is racy.
            if (!$this->hasIndex('orders', 'orders_booking_id_unique')) {
                $table->unique('booking_id', 'orders_booking_id_unique');
            }
            if (!$this->hasIndex('orders', 'orders_customer_id_index')) {
                $table->index('customer_id', 'orders_customer_id_index');
            }
            if (!$this->hasIndex('orders', 'orders_rider_id_index')) {
                $table->index('rider_id', 'orders_rider_id_index');
            }
            if (!$this->hasIndex('orders', 'orders_order_status_index')) {
                $table->index('order_status', 'orders_order_status_index');
            }
            if (!$this->hasIndex('orders', 'orders_picked_time_index')) {
                $table->index('picked_time', 'orders_picked_time_index');
            }
            // Composite for the dispatcher: "rider's open work, oldest first".
            if (!$this->hasIndex('orders', 'orders_rider_status_index')) {
                $table->index(['rider_id', 'order_status'], 'orders_rider_status_index');
            }
            if (!$this->hasIndex('orders', 'orders_customer_created_index')) {
                $table->index(['customer_id', 'created_at'], 'orders_customer_created_index');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!$this->hasIndex('payments', 'payments_order_id_index')) {
                $table->index('order_id', 'payments_order_id_index');
            }
            if (!$this->hasIndex('payments', 'payments_status_index')) {
                $table->index('status', 'payments_status_index');
            }
        });

        Schema::table('order_sub_trips', function (Blueprint $table) {
            if (!$this->hasIndex('order_sub_trips', 'order_sub_trips_order_id_index')) {
                $table->index('order_id', 'order_sub_trips_order_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_sub_trips', function (Blueprint $table) {
            if ($this->hasIndex('order_sub_trips', 'order_sub_trips_order_id_index')) {
                $table->dropIndex('order_sub_trips_order_id_index');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if ($this->hasIndex('payments', 'payments_status_index')) {
                $table->dropIndex('payments_status_index');
            }
            if ($this->hasIndex('payments', 'payments_order_id_index')) {
                $table->dropIndex('payments_order_id_index');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'orders_customer_created_index',
                'orders_rider_status_index',
                'orders_picked_time_index',
                'orders_order_status_index',
                'orders_rider_id_index',
                'orders_customer_id_index',
            ] as $idx) {
                if ($this->hasIndex('orders', $idx)) {
                    $table->dropIndex($idx);
                }
            }
            if ($this->hasIndex('orders', 'orders_booking_id_unique')) {
                $table->dropUnique('orders_booking_id_unique');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'confirmation_code_expires_at')) {
                $table->dropColumn('confirmation_code_expires_at');
            }
            if (Schema::hasColumn('users', 'forget_code_expires_at')) {
                $table->dropColumn('forget_code_expires_at');
            }
        });
    }

    /**
     * Driver-agnostic index-existence check. doctrine/dbal is in composer.json
     * which is what powers Schema::hasIndex internally on Laravel 8.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        try {
            $indexes = $sm->listTableIndexes($table);
        } catch (\Throwable $e) {
            return false;
        }
        return array_key_exists(strtolower($indexName), array_change_key_case($indexes));
    }
};
