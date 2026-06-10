<?php

namespace App\Console\Commands;

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

    protected $description = 'Assign pending orders to the nearest available rider';

    private const BATCH_SIZE           = 50;
    private const MAX_DISTANCE_KM      = 20;
    private const STALE_GPS_MINUTES    = 15;
    private const REASSIGNABLE_STATUSES = ['rejected', 'deleted'];
    private const ACTIVE_ORDER_STATUSES = ['picking', 'picked_up', 'on_way'];

    public function __construct()
    {
        parent::__construct();
    }

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

            $excludeRiderId = 0;
            if ($lastAssign) {
                if (in_array($lastAssign->assign_status, self::REASSIGNABLE_STATUSES, true)) {
                    $excludeRiderId = (int) $lastAssign->rider_id;
                } else {
                    continue;
                }
            }

            $assignedRider = $this->assignToNearestRider(
                (int) $order->id,
                $excludeRiderId,
                $assignedInBatch
            );

            if ($assignedRider !== null) {
                $assignedInBatch[] = $assignedRider;
                $this->info('Assigned order ' . $order->id . ' to rider ' . $assignedRider);
            } else {
                $this->info('No available rider for order ' . $order->id);
            }
        }
    }

    private function assignToNearestRider(int $orderId, int $excludeRiderId, array $assignedInBatch): ?int
    {
        $orderTrip = OrderSubTrip::where('order_id', $orderId)
            ->orderBy('id', 'ASC')
            ->first();

        if (!$orderTrip) {
            return null;
        }

        $orderLat = (float) $orderTrip->start_lat;
        $orderLng = (float) $orderTrip->start_long;

        $excludeIds = $this->buildExclusionList($excludeRiderId, $assignedInBatch);

        $distanceFormula = '( 6371 * acos( cos( radians(?) ) * cos( radians( u.lat ) ) '
            . '* cos( radians( u.long ) - radians(?) ) '
            . '+ sin( radians(?) ) * sin( radians( u.lat ) ) ) )';

        $staleThreshold = Carbon::now()->subMinutes(self::STALE_GPS_MINUTES);

        $query = User::from('users as u')
            ->select('u.id', 'u.first_name', 'u.last_name', 'u.email', 'u.profile_image', 'u.fcm_token')
            ->selectRaw($distanceFormula . ' AS distance', [$orderLat, $orderLng, $orderLat])
            ->where('u.role_id', 2)
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
            ->orderBy('distance', 'ASC');

        if (!empty($excludeIds)) {
            $query->whereNotIn('u.id', $excludeIds);
        }

        $nearest = $query->first();

        if (!$nearest) {
            return null;
        }

        OrderAssign::create([
            'order_id'      => $orderId,
            'rider_id'      => $nearest->id,
            'assign_status' => 'pending',
        ]);

        $order = Order::find($orderId);
        if ($order) {
            $order->forceFill(['is_assign' => 1])->save();
        }

        try {
            FireBaseMessaging::send_notification(
                $nearest->fcm_token,
                'New booking nearby — Booking-Id: ' . $orderId,
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

    private function buildExclusionList(int $excludeRiderId, array $assignedInBatch): array
    {
        // Riders already holding an outstanding pending assignment
        $pendingAssignmentIds = OrderAssign::where('assign_status', 'pending')
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
            [$excludeRiderId]
        );

        return array_values(array_unique(array_filter(
            $exclude,
            fn ($id) => (int) $id > 0
        )));
    }
}
