<?php

namespace App\Scopes;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderFilter
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder)
    {
        if ($this->request->has('statusFilter') && $this->request->statusFilter != 'all') {
            $builder->where('o.order_status', $this->request->statusFilter);
        }

        if ($this->request->has('orderFilterType') && $this->request->orderFilterType != 'all') {
            if ($this->request->orderFilterType == 'withouthelper') {
                $builder->whereNull('hp.order_id');
            } elseif ($this->request->orderFilterType == 'withhelper') {
                $builder->whereNotNull('hp.order_id');
            }
        }

        $action = $this->request->get('action', 'pickup');

        if ($action == 'pickup') {
            $builder->whereNotIn('o.order_status', [Order::STATUS_ACCIDENT, Order::STATUS_REFUSED, Order::STATUS_DELIVERED, Order::STATUS_CANCEL])
                ->orderBy('o.picked_time', 'asc');
        } elseif ($action == 'schedule') {
            $builder->whereDate('o.picked_time', '>', Carbon::now())
                ->orderBy('o.picked_time', 'asc');
        } elseif ($action == 'history') {
            $builder->whereIn('o.order_status', [Order::STATUS_ACCIDENT, Order::STATUS_REFUSED, Order::STATUS_DELIVERED, Order::STATUS_CANCEL])
                ->orderBy('o.updated_at', 'desc');
        } elseif ($action == 'all') {
            // Don't apply action-based filters, just order by created_at
            $builder->orderBy('o.created_at', 'desc');
        }

        return $builder;
    }
}
