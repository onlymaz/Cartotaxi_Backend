<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderAssign;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OrderAssignExpire extends Command
{
    protected $signature = 'assign:order_expire';

    protected $description = 'Expire pending order assignments older than 4 minutes so the order re-queues';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $assign_orders = OrderAssign::where('assign_status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subMinutes(4)->format('Y-m-d H:i:s'))
            ->get();

        foreach ($assign_orders as $order) {
            $order_id = $order->order_id;
            $order->update(['assign_status' => 'deleted']);

            $update_order = Order::find($order_id);
            if ($update_order) {
                $update_order->forceFill(['is_assign' => 0])->save();
            }

            $this->info('Restored order: ' . $order_id);
        }
    }
}
