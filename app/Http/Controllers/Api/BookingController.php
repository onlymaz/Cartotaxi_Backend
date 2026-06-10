<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Gateway;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderSubTrip;
use App\Models\Package;
use App\Models\Payment;
use App\Utilities\UserHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Utilities\FireBaseMessaging;
use App\Mail\StoreBooking;
use App\Jobs\CreateBooking;
use App\Mail\AssignRider;
use App\Jobs\SendAssignRider;
use App\Models\Helper;
use App\Models\HelperOrder;
use App\Models\Notification;
use App\Jobs\SendUpdateStatus;
use App\Mail\UpdateStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Scopes\BookingFilter;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request)
    {
        $locations      = json_decode($request->location, true);
        $package_detail = Package::findOrFail($request->package_id);
        $customer_id    = $request->user()->id;
        $totalMeter     = (int) $request->total_meter;
        $totalSecond    = (int) $request->total_second;

        // Server-side computation of total_amount. Client-supplied values are
        // ignored — that closes the "POST total_amount=0.01" payment-fraud
        // vector. Helper cost is added when the booking includes a helper.
        $kilometers  = max(0, $totalMeter / 1000);
        $totalAmount = round((float) $package_detail->fixed_price
            + (float) $package_detail->per_km_charges * $kilometers, 2);
        if ($request->boolean('with_helper')) {
            // Helper cost is a function of fee-per-helper * count, computed from
            // server-side HelperFee + the requested headcount.
            $helperCount = max(0, (int) $request->total_helper);
            $helperFee   = (float) optional(\App\Models\HelperFee::first())->fee;
            $totalAmount += round($helperFee * $helperCount, 2);
        }

        // Wrap the multi-row write in a transaction so a partial failure
        // (Helper insert fails after Order/Payment land) cannot leave orphans.
        $order = DB::transaction(function () use (
            $request, $package_detail, $customer_id,
            $totalMeter, $totalSecond, $totalAmount, $locations
        ) {
            // Order::forceCreate — customer_id/rider_id/order_status/total_amount/
            // fixed_price/booking_id are NOT in $fillable. Setting them via
            // forceCreate is the documented escape hatch for server-controlled
            // values. Client cannot reach these fields through mass assignment.
            $order = Order::forceCreate([
                'booking_id'        => Order::CreateRandomBookingID(),
                'customer_id'       => $customer_id,
                'package_id'        => $request->package_id,
                'rider_id'          => 0,
                'start_district_id' => 1,
                'end_district_id'   => 1,
                'name'              => '',
                'description'       => $request->description ?? '',
                'start_location'    => $locations[0]['start_location'],
                'end_location'      => end($locations)['end_location'],
                'picked_time'       => Carbon::parse($request->picked_time),
                'fixed_price'       => $package_detail->fixed_price,
                'per_km_charges'    => $package_detail->per_km_charges,
                'total_amount'      => $totalAmount,
                'total_meter'       => $totalMeter,
                'total_second'      => $totalSecond,
                'order_status'      => Order::STATUS_PROCESSING,
                'map_image'         => $request->poly_points,
            ]);

            $subTrips = [];
            foreach ($locations as $location) {
                $subTrips[] = [
                    'order_id'          => $order->id,
                    'start_district_id' => 1,
                    'end_district_id'   => 1,
                    'start_location'    => $location['start_location'],
                    'end_location'      => $location['end_location'],
                    'start_lat'         => $location['start_lat'],
                    'start_long'        => $location['start_long'],
                    'end_lat'           => $location['end_lat'],
                    'end_long'          => $location['end_long'],
                    'total_amount'      => $totalAmount,
                    'total_meter'       => $totalMeter,
                    'total_second'      => $totalSecond,
                    'created_at'        => Carbon::now(),
                    'updated_at'        => Carbon::now(),
                ];
            }
            OrderSubTrip::insert($subTrips);

            OrderStatus::create([
                'order_id'     => $order->id,
                'order_status' => Order::STATUS_PROCESSING,
                'comments'     => '',
            ]);

            // Payment is created in `pending` regardless of payment_type.
            // The actual gateway/webhook flow is responsible for promoting to
            // `completed` — never trust the client's word on payment success.
            Payment::create([
                'order_id'       => $order->id,
                'customer_id'    => $customer_id,
                'gateway_id'     => 1,
                'amount'         => $totalAmount,
                'transactions'   => $request->transaction_id,
                'transaction_id' => $request->transaction_id,
                'response'       => $request->response,
                'status'         => 'pending',
            ]);

            if ($request->boolean('with_helper')) {
                $helper = Helper::create([
                    'user_id'        => $customer_id,
                    'total_helper'   => (int) $request->total_helper,
                    'payment_method' => 1,
                    'price'          => (double) $request->helper_cost,
                    'address'        => end($locations)['end_location'],
                    'start_time'     => Carbon::parse($request->picked_time),
                    'end_time'       => (int) $request->hours,
                ]);

                HelperOrder::create([
                    'order_id'  => $order->id,
                    'helper_id' => $helper->id,
                ]);
            }

            return $order;
        });

        // Notifications happen outside the transaction — a failure to push to
        // FCM/Firebase shouldn't roll back a successful booking.
        $customer_name = User::where('id', $customer_id)->first();
        $admins        = User::where('role_id', '1')->get();
        foreach ($admins as $admin) {
            FireBaseMessaging::send_notification(
                $admin->fcm_web_token,
                "Customer Booking",
                "Customer Create a new Booking-Name: " . $customer_name->first_name . " " . $customer_name->last_name . " Booking-Id: " . $order->id . " ",
                $order
            );
            CreateBooking::dispatch($admin, new StoreBooking($admin, $order->id));

            Notification::create([
                'user_id'            => $request->user()->id,
                'user_to_notify'     => $admin->id,
                'notifications_text' => "Customer Create a new Booking-Name: " . $customer_name->first_name . " " . $customer_name->last_name . " Booking-Id: " . $order->id . " ",
            ]);
        }

        CreateBooking::dispatch($customer_name, new StoreBooking($customer_name, $order->id));

        Notification::create([
            'user_id'            => $request->user()->id,
            'user_to_notify'     => $customer_id,
            'notifications_text' => "Your Order has been Placed",
        ]);

        return response()->json([
            'status'   => true,
            'messages' => "Order Created Successfully",
            'data'     => $order,
        ]);
    }

    public function bookings(Request $request, BookingFilter $filters)
    {
        // leftJoin on payments/gateways: an order without a payment row should
        // still appear in the listing (previously it was silently dropped).
        $bookings = Order::filter($filters)
            ->join('packages as p', 'p.id', '=', 'orders.package_id')
            ->join('users as u', 'u.id', '=', 'orders.customer_id')
            ->leftJoin('payments as pa', 'pa.order_id', '=', 'orders.id')
            ->leftJoin('gateways as g', 'g.id', '=', 'pa.gateway_id')
            ->leftjoin('users as r', 'r.id', '=', 'orders.rider_id')
            ->select(
                'orders.start_location',
                'p.name as package_name',
                'p.weight as packet_size',
                'p.unit as package_unit',
                'orders.map_image as map_img',
                'orders.order_status',
                'orders.booking_id',
                'orders.end_location',
                'orders.total_meter as total_metter',
                'orders.total_amount as total_amount',
                'orders.picked_time',
                'orders.start_time',
                'orders.end_time',
                'p.weight',
                'p.unit',
                'r.first_name as rider_first_name',
                'r.last_name as rider_last_name',
                'r.profile_image as rider_image',
                'u.profile_image as user_image',
                'u.first_name',
                'u.last_name',
                'r.id as rider_id',
                'u.phone_number',
                'r.phone_number as rider_number',
                'r.car_number as car_number',
                'orders.id',
                'pa.status as payment_status',
                'g.name as gateway_name',
                'orders.created_at as booking_created'
            )
            ->orderBy('orders.updated_at', 'DESC')
            ->paginate(20);

        return response()->json([
            'status'   => true,
            'messages' => 'Bookings',
            'data'     => $bookings,
        ]);
    }

    public function update_status(UpdateOrderStatusRequest $request)
    {
        $routeOrderId = (int) $request->route('id');
        $bodyOrderId = $request->filled('order_id') ? (int) $request->order_id : $routeOrderId;

        if ($routeOrderId !== $bodyOrderId) {
            return response()->json([
                'status'   => false,
                'messages' => 'Order id mismatch.',
            ], 422);
        }

        $order = Order::find($routeOrderId);

        if (!$order) {
            return response()->json(['status' => false, 'messages' => 'Order not found'], 404);
        }

        // Authorisation: riders update only orders assigned to them;
        // customers update only their own orders. Admins (role 1) bypass.
        $user = $request->user();
        if ((int)$user->role_id === 2 && (int)$order->rider_id !== (int)$user->id) {
            return response()->json([
                'status'   => false,
                'messages' => 'You are not authorised to update this order.'
            ], 403);
        }
        if ((int)$user->role_id === 3 && (int)$order->customer_id !== (int)$user->id) {
            return response()->json([
                'status'   => false,
                'messages' => 'You are not authorised to update this order.'
            ], 403);
        }
        if ((int)$user->role_id === 3
            && $request->order_status !== Order::STATUS_CANCEL
            && !$this->isLocalSimulatorDeliveryCompletion($request, $order)) {
            return response()->json([
                'status'   => false,
                'messages' => 'Customers can only cancel their own order.'
            ], 403);
        }

        $start_time = $order->start_time;
        $end_time   = $order->end_time;

        // Whitelist of statuses any non-admin can transition to. Admins (role 1)
        // bypass the whitelist; clients on the mobile API cannot use this
        // endpoint to mark themselves "delivered" arbitrarily.
        $clientAllowedStatuses = ['picking', 'picked_up', 'on_way', 'delivered', 'refused', 'not_received', 'cancel'];
        if ((int) $user->role_id !== 1
            && !in_array($request->order_status, $clientAllowedStatuses, true)) {
            return response()->json([
                'status'   => false,
                'messages' => 'Invalid status transition.',
            ], 422);
        }

        if ($request->order_status == 'on_way') {
            $start_time = date('Y-m-d H:i:s');
        }
        if (in_array($request->order_status, ['delivered', 'refused', 'not_received', 'cancel'])) {
            $end_time = date('Y-m-d H:i:s');
        }

        DB::transaction(function () use ($order, $request, $start_time, $end_time) {
            // forceFill bypasses $fillable, which intentionally excludes
            // order_status to prevent mass-assignment of workflow state.
            $order->forceFill([
                'order_status' => $request->order_status,
                'start_time'   => $start_time,
                'end_time'     => $end_time,
            ])->save();

            $orderStatus = OrderStatus::firstOrNew(['order_id' => $order->id]);
            $orderStatus->order_status = $request->order_status;
            if (!$orderStatus->exists) {
                $orderStatus->comments = '';
            }
            $orderStatus->save();
        });

        $order->refresh();

        $booking = Order::from(get_table_name(Order::class) . ' as o')
            ->join(get_table_name(Package::class) . ' as p', 'p.id', 'o.package_id')
            ->join(get_table_name(User::class) . ' as u', 'u.id', 'o.customer_id')
            ->leftjoin(get_table_name(User::class) . ' as r', 'r.id', 'o.rider_id')
            ->leftJoin(get_table_name(Payment::class) . ' as pa', 'pa.order_id', 'o.id')
            ->leftJoin(get_table_name(Gateway::class) . ' as g', 'g.id', 'pa.gateway_id')
            ->where('o.id', '=', $order->id)
            ->select(
                'o.start_location', 'o.end_location', 'o.total_meter', 'o.total_amount',
                'o.picked_time', 'o.start_time', 'o.end_time', 'o.order_status',
                'p.weight', 'p.unit',
                'r.first_name as rider_first_name', 'r.last_name as rider_last_name',
                'r.profile_image as rider_image', 'r.phone_number as rider_number',
                'r.car_number as car_number',
                'u.first_name', 'u.last_name', 'u.profile_image as user_image',
                'r.id as rider_id', 'u.phone_number', 'o.id',
                'pa.status as payment_status', 'g.name as gateway_name', 'o.customer_id'
            )
            ->first();

        $is_past = $booking->picked_time < Carbon::today();
        $name    = ($user->role_id == 3)
            ? $booking->rider_first_name . ' ' . $booking->rider_last_name
            : $booking->first_name . ' ' . $booking->last_name;
        $phone_number = ($user->role_id == 3)
            ? $booking->rider_number
            : $booking->phone_number;
        $imgUser = ($user->role_id == 3)
            ? $booking->rider_image
            : $booking->user_image;

        $data['bookings']['list'] = [
            'order_id'       => $booking->id,
            'name'           => $name,
            'rider_id'       => !empty($booking->rider_id) ? $booking->rider_id : 0,
            'pick_up'        => $booking->start_location,
            'drop_off'       => $booking->end_location,
            'total_distance' => number_format((float)$booking->total_meter / 1000, 2, '.', '') . ' KM',
            'packet_size'    => $booking->weight . $booking->unit,
            'total_cost'     => number_format((float)$booking->total_amount, 2, '.', ''),
            'ride_start'     => !empty($booking->start_time) ? utc_time(strtotime($booking->start_time)) : '',
            'end_ride'       => !empty($booking->end_time) ? utc_time(strtotime($booking->end_time)) : '',
            'order_status'   => $booking->order_status,
            'is_past'        => $is_past,
            'phone_number'   => !empty($phone_number) ? $phone_number : '',
            'rider_number'   => !empty($booking->rider_number) ? $booking->rider_number : '',
            'car_number'     => !empty($booking->car_number) ? $booking->car_number : '',
            'user_image'     => !empty($imgUser) ? url($imgUser) : url('/images/placeholder.jpg'),
            'payment_status' => !empty($booking->payment_status) ? $booking->payment_status : '',
            'gateway_name'   => !empty($booking->gateway_name) ? $booking->gateway_name : '',
        ];
        $data['user'] = UserHelper::user_stats($user);

        $currentStatus = $request->order_status;
        $customer_text = match($currentStatus) {
            'picking'      => 'Rider is coming to you',
            'picked_up'    => 'Rider has arrived',
            'on_way'       => 'Your package is on the way',
            'delivered'    => 'Your package has been delivered',
            'cancel'       => 'Your ride has been cancelled',
            'not_received' => 'Package has not been received',
            'refused'      => 'Package has been refused',
            default        => '',
        };

        $customer = User::find($booking->customer_id);
        $messages = UserHelper::orderStatus($request->order_status);

        FireBaseMessaging::send_notification(
            $customer->fcm_token,
            "Order Id :" . $order->id,
            'Rider Update Status : ' . $messages,
            $order
        );
        FireBaseMessaging::send_notification(
            $customer->fcm_web_token,
            "Order Id :" . $order->id,
            'Rider Update Status : ' . $messages,
            $order
        );

        Notification::create([
            'user_id'            => $request->user()->id,
            'user_to_notify'     => $customer->id,
            'notifications_text' => "Order Id : " . $order->id . '  Rider Update Status : ' . $order->order_status,
        ]);

        SendUpdateStatus::dispatch(
            $customer,
            new UpdateStatus($customer, $order->id, str_replace("_", " ", $messages), "Rider"),
            str_replace("_", " ", $messages),
            "Rider"
        );

        return response()->json([
            'status'   => true,
            'messages' => 'Successfully updated status',
            'data'     => $data
        ]);
    }

    private function isLocalSimulatorDeliveryCompletion(UpdateOrderStatusRequest $request, Order $order): bool
    {
        return app()->environment(['local', 'testing'])
            && $request->boolean('simulated_tracking')
            && $request->order_status === Order::STATUS_DELIVERED
            && (int) $order->rider_id > 0
            && (int) $order->is_assign === 1;
    }

    public function update_payment(UpdatePaymentRequest $request)
    {
        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['status' => false, 'messages' => 'Order not found'], 404);
        }

        // Only the assigned rider or an admin may finalize payment + delivery.
        // Customers MUST NOT be able to flip their own order to delivered/paid
        // — that was the original P0 fraud vector.
        $user = $request->user();
        $isAdmin    = (int) $user->role_id === 1;
        $isAssigned = (int) $order->rider_id === (int) $user->id;

        if (!$isAdmin && !$isAssigned) {
            return response()->json(['status' => false, 'messages' => 'Not authorized'], 403);
        }

        $payment = Payment::where('order_id', $request->order_id)->first();

        if ($payment) {
            $payment->update(['status' => 'completed']);

            $imagePath = null;
            if ($request->hasFile('sign_image')) {
                $imagePath = $request->file('sign_image')->store('signatures', 'public');
            }

            Order::where('id', $request->order_id)->update([
                'sign'         => $imagePath,
                'end_time'     => date('Y-m-d H:i:s'),
                'order_status' => 'delivered',
            ]);

            if ($request->another_reciver == true) {
                Order::where('id', $request->order_id)->update([
                    'reciver_name'    => $request->reciver_name,
                    'reciver_address' => $request->reciver_address,
                ]);
            }

            $order    = Order::where('id', $request->order_id)->first();
            $customer = User::find($order->customer_id);
            $messages = UserHelper::orderStatus('delivered');

            FireBaseMessaging::send_notification(
                $customer->fcm_token,
                "Order Id :" . $order->id,
                'Rider Update Status : ' . $messages,
                $order
            );
            FireBaseMessaging::send_notification(
                $customer->fcm_web_token,
                "Order Id :" . $order->id,
                'Rider Update Status : ' . $messages,
                $order
            );

            Notification::create([
                'user_id'            => $request->user()->id,
                'user_to_notify'     => $customer->id,
                'notifications_text' => "Order Id : " . $order->id . ' Rider Update Status : ' . $order->order_status,
            ]);

            SendUpdateStatus::dispatch(
                $customer,
                new UpdateStatus($customer, $order->id, str_replace("_", " ", $messages), "Rider"),
                str_replace("_", " ", $messages),
                "Rider"
            );
        }

        return response()->json([
            'status'   => true,
            'messages' => 'Updated Successfully'
        ]);
    }
}
