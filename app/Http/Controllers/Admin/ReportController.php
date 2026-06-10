<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $rangeKey] = $this->resolveRange($request);

        $baseQuery = Order::whereBetween('created_at', [$from, $to]);

        $totalOrders     = (clone $baseQuery)->count();
        $totalRevenue    = (float) (clone $baseQuery)->whereIn('order_status', ['delivered', 'on_way', 'picked_up'])->sum('total_amount');
        $deliveredCount  = (clone $baseQuery)->where('order_status', 'delivered')->count();
        $activeRiders    = (clone $baseQuery)->where('rider_id', '!=', 0)->distinct('rider_id')->count('rider_id');

        $statusBreakdown = (clone $baseQuery)
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->groupBy('order_status')
            ->orderByDesc('total')
            ->get();

        $topRiders = Order::query()
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.rider_id', '!=', 0)
            ->where('orders.order_status', 'delivered')
            ->join('users', 'users.id', '=', 'orders.rider_id')
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                DB::raw('COUNT(orders.id) as completed_orders'),
                DB::raw('SUM(orders.total_amount) as revenue')
            )
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderByDesc('completed_orders')
            ->limit(5)
            ->get();

        $revenueTrend = $this->buildRevenueTrend($from, $to);

        return view('admin.reports.index', compact(
            'totalOrders',
            'totalRevenue',
            'deliveredCount',
            'activeRiders',
            'statusBreakdown',
            'topRiders',
            'revenueTrend',
            'from',
            'to',
            'rangeKey'
        ));
    }

    private function resolveRange(Request $request): array
    {
        $rangeKey = $request->input('range', '30d');
        $to       = Carbon::now()->endOfDay();

        switch ($rangeKey) {
            case 'today':
                $from = Carbon::now()->startOfDay();
                break;
            case '7d':
                $from = Carbon::now()->subDays(6)->startOfDay();
                break;
            case 'custom':
                $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
                $to   = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : $to;
                break;
            case '30d':
            default:
                $rangeKey = '30d';
                $from = Carbon::now()->subDays(29)->startOfDay();
                break;
        }

        return [$from, $to, $rangeKey];
    }

    private function buildRevenueTrend(Carbon $from, Carbon $to): array
    {
        $rows = Order::whereBetween('created_at', [$from, $to])
            ->whereIn('order_status', ['delivered', 'on_way', 'picked_up'])
            ->select(
                DB::raw("DATE(created_at) as day"),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get()
            ->keyBy('day');

        $labels       = [];
        $revenueData  = [];
        $ordersData   = [];
        $cursor       = $from->copy()->startOfDay();
        $endDay       = $to->copy()->startOfDay();

        while ($cursor->lte($endDay)) {
            $key = $cursor->format('Y-m-d');
            $labels[]      = $cursor->format('M d');
            $revenueData[] = isset($rows[$key]) ? (float) $rows[$key]->revenue : 0;
            $ordersData[]  = isset($rows[$key]) ? (int) $rows[$key]->orders_count : 0;
            $cursor->addDay();
        }

        return compact('labels', 'revenueData', 'ordersData');
    }
}
