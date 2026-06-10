<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendConfirmationEmail;
use App\Jobs\SendResetPasswordEmail;
use App\Mail\ResetPasswordEmail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Utilities\TwilioHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Build query with date filters
        $userQuery = User::query();
        $orderQuery = Order::query();
        $paymentQuery = Payment::query();
        
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            
            // Filter orders by date
            $orderQuery->whereBetween('created_at', [$start, $end]);
            
            // Filter payments by date
            $paymentQuery->whereBetween('created_at', [$start, $end]);
            
            // Filter users by registration date
            $userQuery->whereBetween('created_at', [$start, $end]);
        }
        
        // Calculate stats with date filters
        $stats = [
            'total_customers' => (clone $userQuery)->where('role_id', 3)->count(),
            'total_riders' => (clone $userQuery)->where('role_id', 2)->count(),
            'verified_customers' => (clone $userQuery)->where('role_id', 3)->where('confirmed', 1)->count(),
            'unverified_customers' => (clone $userQuery)->where('role_id', 3)->where('confirmed', 0)->count(),
            'total_delivered' => (clone $orderQuery)->where('order_status', 'delivered')->count(),
            'total_accident_bookings' => (clone $orderQuery)->where('order_status', 'accident')->count(),
            'on_way_bookings' => (clone $orderQuery)->where('order_status', 'on_way')->count(),
            'revenue' => (clone $paymentQuery)->where('status', 'completed')->sum('amount'),
            'pending_revenue' => (clone $paymentQuery)->where('status', 'pending')->sum('amount'),
            'total_cancelled_bookings' => (clone $orderQuery)->where('order_status', 'cancel')->count(),
            'scheduled_bookings' => (clone $orderQuery)->whereDate('picked_time', '>', Carbon::today())->count(),
            'COD' => (clone $paymentQuery)->where('transactions', 'viaCod')->where('status', 'completed')->sum('amount'),
            'weekly' => (clone $paymentQuery)->where('transactions', 'viaWeekly')->where('status', 'completed')->sum('amount'),
            'paypal' => (clone $paymentQuery)->where('transactions', 'viaPaypal')->where('status', 'completed')->sum('amount'),
            'stripe' => (clone $paymentQuery)->where('transactions', 'viaStripe')->where('status', 'completed')->sum('amount'),
        ];

        $orders = (clone $orderQuery)
                        ->with('user')
                        ->select('id', 'customer_id', 'picked_time', 'order_status', 'booking_id', 'created_at')
                        ->orderBy('created_at','DESC')
                        ->paginate(10)
                        ->appends($request->query());
        
        // Use Cargo Taxi branded dashboard
        return view('admin.ct-dashboard', compact('stats', 'orders', 'startDate', 'endDate'));
    }
}
