<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\ReviewRating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store_rating(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'rating'   => 'required|numeric|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($request->order_id);
        $user  = $request->user();

        if ($order->order_status !== 'delivered') {
            return response()->json([
                'status'   => false,
                'messages' => 'You can only rate a completed order.',
            ], 422);
        }

        $isCustomer = (int) $order->customer_id === (int) $user->id;
        $isRider    = (int) $order->rider_id    === (int) $user->id;

        if (!$isCustomer && !$isRider) {
            return response()->json([
                'status'   => false,
                'messages' => 'You are not authorised to rate this order.',
            ], 403);
        }

        $alreadyRated = Review::where('user_id', $user->id)
            ->where('order_id', $request->order_id)
            ->exists();

        if ($alreadyRated) {
            return response()->json([
                'status'   => false,
                'messages' => 'You have already rated this order.',
            ], 422);
        }

        // Customer reviewing the rider; rider reviewing the customer
        $type   = $isRider ? 'rider'  : 'customer';
        $typeId = $isRider ? 1        : 2;

        $review = Review::create([
            'user_id'  => $user->id,
            'order_id' => $request->order_id,
            'types'    => $type,
            'status'   => 1,
            'approved' => 1,
        ]);

        ReviewRating::create([
            'user_id'        => $user->id,
            'order_id'       => $request->order_id,
            'review_id'      => $review->id,
            'review_type_id' => $typeId,
            'rating'         => $request->rating,
            'comments'       => $request->comments,
        ]);

        return response()->json([
            'status'   => true,
            'messages' => 'Rating submitted successfully.',
        ]);
    }
}
