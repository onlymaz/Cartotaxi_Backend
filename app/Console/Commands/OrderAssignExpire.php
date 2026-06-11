<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderAssign;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OrderAssignExpire extends Command
{
    protected $signature = 'assign:order_expire';

    protected $description = 'Expire unanswered ride offers so the order moves to the next nearest rider';

    /** Minutes a rider has to answer the call before it transfers. */
    public const OFFER_TIMEOUT_MINUTES = 1;

    public function handle()
    {
        $assign_orders = OrderAssign::where('assign_status', OrderAssign::STATUS_PENDING)
            ->where('created_at', '<=', Carbon::now()->subMinutes(self::OFFER_TIMEOUT_MINUTES))
            ->get();

        foreach ($assign_orders as $offer) {
            $offer->update([
                'assign_status' => OrderAssign::STATUS_EXPIRED,
                'note'          => 'No answer within ' . self::OFFER_TIMEOUT_MINUTES . ' minutes — transferred to next rider',
            ]);

            $update_order = Order::find($offer->order_id);
            if ($update_order) {
                $update_order->forceFill(['is_assign' => 0])->save();
            }

            $this->info('Offer ' . $offer->id . ' (order ' . $offer->order_id . ', rider ' . $offer->rider_id . ') expired — order re-queued');
        }
    }
}
