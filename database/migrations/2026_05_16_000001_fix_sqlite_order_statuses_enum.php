<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixSqliteOrderStatusesEnum extends Migration
{
    private const STATUSES = [
        'pending',
        'processing',
        'picking',
        'picked_up',
        'on_way',
        'accident',
        'not_received',
        'refused',
        'delivered',
        'cancel',
    ];

    public function up()
    {
        if (DB::getDriverName() !== 'sqlite' || !Schema::hasTable('order_statuses')) {
            return;
        }

        $this->rebuildOrderStatusesTable(self::STATUSES);
    }

    public function down()
    {
        // Rollback would need to drop valid app states from existing rows.
    }

    private function rebuildOrderStatusesTable(array $statuses): void
    {
        $statusList = implode("', '", array_map(static function ($status) {
            return str_replace("'", "''", $status);
        }, $statuses));

        DB::statement('PRAGMA foreign_keys=OFF');
        DB::beginTransaction();

        try {
            DB::statement("
                CREATE TABLE order_statuses_new (
                    id integer not null primary key autoincrement,
                    order_id integer not null default '0',
                    order_status varchar check (order_status in ('{$statusList}')) not null default 'pending',
                    comments text,
                    created_at datetime,
                    updated_at datetime
                )
            ");
            DB::statement('
                INSERT INTO order_statuses_new (id, order_id, order_status, comments, created_at, updated_at)
                SELECT id, order_id, order_status, comments, created_at, updated_at
                FROM order_statuses
            ');
            DB::statement('DROP TABLE order_statuses');
            DB::statement('ALTER TABLE order_statuses_new RENAME TO order_statuses');

            DB::commit();
        } catch (Throwable $error) {
            DB::rollBack();
            throw $error;
        } finally {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }
}
