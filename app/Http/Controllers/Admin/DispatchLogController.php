<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\AssignOrderToRider;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAssign;
use Illuminate\Http\Request;

class DispatchLogController extends Controller
{
    /**
     * Central audit trail of the auto-dispatch cron: every ride offer,
     * transfer, acceptance, rejection and manual assignment.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q      = trim((string) $request->query('q'));

        $logs = OrderAssign::with(['rider', 'order'])
            ->when($status === 'expired', fn ($query) => $query->whereIn('assign_status', ['expired', 'deleted']))
            ->when($status && $status !== 'expired', fn ($query) => $query->where('assign_status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->whereHas('order', fn ($o) => $o->where('booking_id', 'like', "%{$q}%")
                            ->orWhere('id', $q))
                        ->orWhereHas('rider', fn ($r) => $r->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'offers_today' => OrderAssign::whereDate('created_at', today())->count(),
            'accepted'     => OrderAssign::where('assign_status', OrderAssign::STATUS_ACCEPTED)->count(),
            'rejected'     => OrderAssign::where('assign_status', OrderAssign::STATUS_REJECTED)->count(),
            'expired'      => OrderAssign::whereIn('assign_status', [OrderAssign::STATUS_EXPIRED, 'deleted'])->count(),
            'waiting'      => OrderAssign::where('assign_status', OrderAssign::STATUS_PENDING)->count(),
            'needs_manual' => Order::where('is_assign', AssignOrderToRider::DISPATCH_EXHAUSTED)
                ->where(function ($query) {
                    $query->whereNull('rider_id')->orWhere('rider_id', 0);
                })->count(),
        ];

        return view('admin.dispatch-logs.index', compact('logs', 'stats', 'status', 'q'));
    }
}
