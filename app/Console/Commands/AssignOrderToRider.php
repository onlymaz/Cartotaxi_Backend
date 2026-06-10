<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderSubTrip;
use App\Models\User;
use App\Models\OrderAssign;
use App\Utilities\FireBaseMessaging;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignOrderToRider extends Command
{
    protected $signature = 'assign:rider';

    protected $description = 'Offer pending orders to the nearest available rider, Uber-style: '
        . 'each rider in the area gets the call once, ordered by distance, until one accepts';

    private const BATCH_SIZE           = 50;
    private const MAX_DISTANCE_KM      = 20;
    private const STALE_GPS_MINUTES    = 15;
    private const REASSIGNABLE_STATUSES = ['rejected', 'expired', 'deleted'];
    private const ACTIVE_ORDER_STATUSES = ['picking', 'picked_up', 'on_way'];

    /** is_assign value meaning "every nearby rider was tried — needs manual dispatch". */
    public const DISPATCH_EXHAUSTED = 2;

    public function handle()
    {
        $orders = Order::whereIn('order_status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
            ->where('is_assign', 0)
            ->where(function ($query) {
                $query->whereNull('rider_id')
                    ->orWhere('rider_id', 0);
            })
            ->orderBy('id', 'DESC')
            ->limit(self::BATCH_SIZE)
            ->get();

        $assignedInBatch = [];

        foreach ($orders as $order) {
            $lastAssign = OrderAssign::where('order_id', $order->id)
                ->orderBy('id', 'DESC')
                ->first();

            // An outstanding pending offer means a rider is still deciding.
            if ($lastAssign && !in_array($lastAssign->assign_status, self::REASSIGNABLE_STATUSES, true)) {
                continue;
            }

            $assignedRider = $this->offerToNearestRider($order, $assignedInBatch);

            if ($assignedRider !== null) {
                $assignedInBatch[] = $assignedRider;
                $this->info('Offered order ' . $order->id . ' to rider ' . $assignedRider);
            } else {
                $this->info('No available rider for order ' . $order->id);
            }
        }
    }

    private function offerToNearestRider(Order $order, array $assignedInBatch): ?int
    {
        $orderTrip = OrderSubTrip::where('order_id', $order->id)
            ->orderBy('id', 'ASC')
            ->first();

        if (!$orderTrip) {
            return null;
        }

        $orderLat = (float) $orderTrip->start_lat;
        $orderLng = (float) $orderTrip->start_long;

        // Every rider who already got the call for this order — the chain only
        // ever moves forward, so nobody is rung twice for the same ride.
        $alreadyOffered = OrderAssign::where('order_id', $order->id)
            ->pluck('rider_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $excludeIds = $this->buildExclusionList($alreadyOffered, $assignedInBatch);

        $distanceFormula = '( 6371 * acos( cos( radians(?) ) * cos( radians( u.lat ) ) '
            . '* cos( radians( u.long ) - radians(?) ) '
            . '+ sin( radians(?) ) * sin( radians( u.lat ) ) ) )';

        $staleThreshold = Carbon::now()->subMinutes(self::STALE_GPS_MINUTES);

        $inRange = User::from('users as u')
            ->select('u.id', 'u.fcm_token')
            ->selectRaw($distanceFormula . ' AS distance', [$orderLat, $orderLng, $orderLat])
            ->where('u.role_id', 2)
            ->where('u.IsActive', 1)
            ->whereNotNull('u.lat')
            ->whereNotNull('u.long')
            ->where('u.lat', '<>', '')
            ->where('u.long', '<>', '')
            ->where('u.updated_at', '>=', $staleThreshold)
            ->whereRaw($distanceFormula . ' <= ?', [
                $orderLat,
                $orderLng,
                $orderLat,
                self::MAX_DISTANCE_KM,
            ])
            ->orderBy('distance', 'ASC')
            ->get();

        $nearest = $inRange->first(fn ($rider) => !in_array((int) $rider->id, $excludeIds, true));

        if (!$nearest) {
            // Every in-range rider has already had the call for this order:
            // stop auto-dispatch and hand the order to a human dispatcher.
            $inRangeIds = $inRange->pluck('id')->map(fn ($id) => (int) $id)->all();
            $exhausted = !empty($alreadyOffered)
                && !empty($inRangeIds)
                && empty(array_diff($inRangeIds, $alreadyOffered));

            if ($exhausted) {
                $this->escalateToAdmins($order, count($alreadyOffered));
            }

            return null;
        }

        OrderAssign::create([
            'order_id'      => $order->id,
            'rider_id'      => $nearest->id,
            'attempt'       => count($alreadyOffered) + 1,
            'distance_km'   => round((float) $nearest->distance, 2),
            'assign_status' => OrderAssign::STATUS_PENDING,
        ]);

        $order->forceFill(['is_assign' => 1])->save();

        try {
            FireBaseMessaging::send_notification(
                $nearest->fcm_token,
                'New booking nearby — Booking-Id: ' . $order->id,
                'Assign Order',
                $order,
                '',
                'new_order'
            );
        } catch (\Throwable $ex) {
            // Swallow push errors; the rider can still pull the order from their feed.
        }

        return (int) $nearest->id;
    }

    private function escalateToAdmins(Order $order, int $attempts): void
    {
        $order->forceFill(['is_assign' => self::DISPATCH_EXHAUSTED])->save();

        $text = 'Auto-dispatch exhausted for order #' . $order->id
            . ' (' . ($order->booking_id ?: 'no booking id') . '): '
            . $attempts . ' rider' . ($attempts === 1 ? '' : 's')
            . ' called, none accepted. Assign a rider manually.';

        foreach (User::where('role_id', 1)->get() as $admin) {
            Notification::create([
                'user_id'        => $admin->id,
                'user_to_notify' => $admin->id,
                'notifications_text' => $text,
            ]);

            try {
                if ($admin->fcm_web_token) {
                    FireBaseMessaging::send_notification($admin->fcm_web_token, $text, 'Dispatch needs attention');
                }
            } catch (\Throwable $ex) {
                // Push is best-effort; the Notification row is the source of truth.
            }
        }

        $this->warn('Dispatch exhausted for order ' . $order->id . ' after ' . $attempts . ' attempts — escalated to admins');
    }

    private function buildExclusionList(array $alreadyOffered, array $assignedInBatch): array
    {
        // Riders already holding an outstanding pending offer
        $pendingAssignmentIds = OrderAssign::where('assign_status', OrderAssign::STATUS_PENDING)
            ->pluck('rider_id')
            ->toArray();

        // Riders mid-delivery on a different order
        $activeOrderIds = Order::whereIn('order_status', self::ACTIVE_ORDER_STATUSES)
            ->whereNotNull('rider_id')
            ->where('rider_id', '>', 0)
            ->pluck('rider_id')
            ->toArray();

        $exclude = array_merge(
            $pendingAssignmentIds,
            $activeOrderIds,
            $assignedInBatch,
            $alreadyOffered
        );

        return array_values(array_unique(array_map('intval', array_filter(
            $exclude,
            fn ($id) => (int) $id > 0
        ))));
    }
}
