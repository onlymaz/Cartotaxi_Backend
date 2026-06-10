<?php

namespace App\Scopes;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BookingFilter
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder)
    {
        $user = $this->request->user();

        // Use the un-aliased table column. The previous "o." prefix was only
        // valid for one specific aliased query elsewhere and broke (or silently
        // dropped the WHERE under permissive SQL modes) when applied to the
        // un-aliased bookings listing — that was the root cause of the
        // IDOR / 500 on the bookings endpoint.
        if ($user->role_id == 3) {
            $builder->where('orders.customer_id', $user->id);
        } else {
            $builder->where('orders.rider_id', $user->id);
        }

        $type = $this->request->type;

        if ($type == 'past') {
            $builder->whereIn('orders.order_status', [
                Order::STATUS_DELIVERED,
                Order::STATUS_ACCIDENT,
                Order::STATUS_REFUSED,
                Order::STATUS_CANCEL,
            ]);
        } elseif ($type == 'upcoming') {
            $builder->whereIn('orders.order_status', [
                Order::STATUS_PICKING,
                'picked_up',
                'on_way',
                'pending',
                Order::STATUS_PROCESSING,
            ]);
        } elseif ($type) {
            $builder->where('orders.order_status', $type);
        }

        return $builder;
    }
}
