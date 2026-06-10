<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReviewRating;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function UserStats(Request $request)
    {
        $user = $request->user();

        if ((int) $user->role_id === 2) {
            $data = $this->riderStats($user->id);
        } else {
            $data = $this->customerStats($user->id);
        }

        return response()->json([
            'status'   => true,
            'messages' => 'Statistics',
            'data'     => $data,
        ]);
    }

    private function customerStats(int $userId): array
    {
        $orderIds = Order::where('customer_id', $userId)->pluck('id');

        return [
            'total_bookings' => Order::where('customer_id', $userId)->count(),
            'completed'      => Order::where('customer_id', $userId)->where('order_status', 'delivered')->count(),
            'cancelled'      => Order::where('customer_id', $userId)
                ->whereIn('order_status', ['cancel', 'refused', 'not_received'])
                ->count(),
            'in_progress'    => Order::where('customer_id', $userId)
                ->whereNotIn('order_status', ['delivered', 'cancel', 'refused', 'not_received', 'pending'])
                ->count(),
            'total_spent'    => round(
                Payment::where('customer_id', $userId)->where('status', 'completed')->sum('amount'),
                2
            ),
            'average_rating' => round(
                ReviewRating::from('review_ratings as rr')
                    ->join('reviews as r', 'r.id', '=', 'rr.review_id')
                    ->where('r.types', 'rider') // riders reviewing this customer
                    ->whereIn('rr.order_id', $orderIds)
                    ->avg('rr.rating') ?? 0,
                2
            ),
        ];
    }

    private function riderStats(int $userId): array
    {
        $orderIds = Order::where('rider_id', $userId)->pluck('id');

        return [
            'total_assigned' => Order::where('rider_id', $userId)->count(),
            'completed'      => Order::where('rider_id', $userId)->where('order_status', 'delivered')->count(),
            'cancelled'      => Order::where('rider_id', $userId)
                ->whereIn('order_status', ['cancel', 'refused', 'not_received'])
                ->count(),
            'in_progress'    => Order::where('rider_id', $userId)
                ->whereNotIn('order_status', ['delivered', 'cancel', 'refused', 'not_received', 'pending'])
                ->count(),
            'total_earned'   => round(
                Payment::where('status', 'completed')->whereIn('order_id', $orderIds)->sum('amount'),
                2
            ),
            'average_rating' => round(
                ReviewRating::from('review_ratings as rr')
                    ->join('reviews as r', 'r.id', '=', 'rr.review_id')
                    ->where('r.types', 'customer') // customers reviewing this rider
                    ->whereIn('rr.order_id', $orderIds)
                    ->avg('rr.rating') ?? 0,
                2
            ),
        ];
    }
}
