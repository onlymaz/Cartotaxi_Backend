<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendFileToRiderRequest;
use App\Jobs\SendAssignRider;
use App\Mail\AssignRider;
use App\Models\Gateway;
use App\Models\HelperOrder;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderSubTrip;
use App\Models\Package;
use App\Models\Payment;
use App\Models\OrderAssign;
use App\Models\User;
use App\Utilities\FireBaseMessaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AutoOrderController extends Controller
{

    public function index(Request $request){
        $user       =   $request->user();
        $bookings   =   OrderAssign::from('order_assigns as oa')
            ->join('orders as o','o.id','=','oa.order_id')
            ->join(get_table_name(Payment::class).' as pa','pa.order_id','o.id')
            ->join(get_table_name(Package::class).' as p','p.id','o.package_id')
            ->join(get_table_name(Gateway::class).' as g','g.id','pa.gateway_id')
            ->leftjoin(get_table_name(User::class).' as u','u.id','o.customer_id')
            ->leftjoin(get_table_name(User::class).' as r','r.id','o.rider_id')
            ->select('o.start_location',
                'p.name as package_name',
                'p.weight as packet_size',
                'p.unit as package_unit',
                'o.map_image as map_img',
                'o.order_status',
                'o.booking_id',
                'o.end_location',
                'o.total_meter as total_metter',
                'o.total_amount as total_amount',
                'o.picked_time',
                'o.start_time',
                'o.end_time',
                'o.order_status',
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
                'o.id',
                'pa.status as payment_status',
                'g.name as gateway_name',
                'o.created_at as booking_created')
            ->where('oa.rider_id',$user->id)
            ->where('oa.assign_status','pending')
            ->orderBy('o.id','DESC')
            ->get();
        $data               =   [];
        if (!empty($bookings) && count($bookings)>0){
            foreach ($bookings as $key  =>  $booking){
                $order_id               =   $booking->id;
                if ($user->role_id==3){
                    $name   =   (!empty($booking->rider_first_name)?$booking->rider_first_name.' '.$booking->rider_last_name : "");
                    $imgUser   =   $booking->rider_image;
                    $phone_number =!empty($booking->rider_number)?$booking->rider_number:'';
                }else{
                    $name= (!empty($booking->first_name)?$booking->first_name.' '.$booking->last_name : "");
                    $imgUser   =   $booking->user_image;
                    $phone_number =!empty($booking->phone_number)?$booking->phone_number:'';
                }
                $data['items'][$key]    =   [
                    'order_id'      =>  $booking->id,
                    'name'          =>  $name,
                    'poly_points'     => !empty((string)$booking->map_img)?(string)$booking->map_img:"",
                    'packet_size'   =>  (string)!empty((string)$booking->packet_size)?(string)$booking->packet_size.$booking->package_unit :0,
                    'rider_id'      => !empty($booking->rider_id)?$booking->rider_id:0,
                    'total_distance'=>  Order::total_km($booking->id)." KM",
                    'total_cost'    =>  !empty($booking->total_amount)? (string)$booking->total_amount :0,
                    'booking_id'    =>  !empty($booking->booking_id)?$booking->booking_id:'',
                    'package_name'  =>  !empty($booking->package_name)?$booking->package_name:'',
                    'order_status'  =>  !empty($booking->order_status)?$booking->order_status:'',
                    'start_location'=>  !empty($booking->start_location)?$booking->start_location:'',
                    'picked_time'   =>  !empty($booking->picked_time)?$booking->picked_time:'',
                    'ride_start'    =>  !empty($booking->start_time)?utc_time(strtotime($booking->start_time)):'',
                    'end_ride'      =>  !empty($booking->end_time)?utc_time(strtotime($booking->end_time)):'',
                    'end_address'   =>  !empty($booking->end_location)?$booking->end_location:'',
                    'booking_created'=> !empty($booking->booking_created)?utc_time(strtotime($booking->booking_created)):"",
                    'phone_number'  =>  $phone_number,
                    'car_number'    =>  (string)$booking->car_number,
                    'payment_status'=>  !empty($booking->payment_status)?$booking->payment_status:'',
                    'gateway_name'  =>  !empty($booking->gateway_name)?$booking->gateway_name:'',
                    'user_image'  =>    !empty($imgUser)?url($imgUser):url('/images/placeholder.jpg'),
                    'rider_number'  =>  !empty((string)$booking->rider_number)?$booking->rider_number:'',
                    'location'      =>  OrderSubTrip::locationObject($booking->id),
                    'helper'        =>  HelperOrder::helperList($booking->id),
                    'is_helper'     =>  HelperOrder::helperStatus($booking->id)
                ];
            }
        }
        else{
            $data['items']  =   [];
        }

        return response()->json([
            'status'   =>  true,
            'data'      =>  $data
        ],200);
    }

    public function allowAutoRider(Request $request){
        $validation_fields  =   [
            'order_id'          =>  'required|numeric',
            'status'            =>  'required|in:accepted,rejected'
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields);
        if($validator->fails()) {
            return response()->json([
                'status'     =>  false,
                'messages'   =>  implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }
        $order_id           =   $request['order_id'];
        $user               =   $request->user();
        $user_id            =   $user->id;
        $order_assign       =   OrderAssign::where('order_id',$order_id)
            ->where('rider_id',$user_id)
            ->where('assign_status','pending')->first();
        if (!empty($request['status']) && $request['status'] === 'accepted'){
            if ($order_assign ){
                $order  =   Order::find($order_id);
                if ($order){
                    $order->forceFill([
                        'rider_id'  => $user_id,
                        'order_status' => Order::STATUS_PICKING,
                        'start_time' => $order->start_time ?: now(),
                    ])->save();

                    OrderStatus::updateOrCreate(
                        ['order_id' => $order->id],
                        ['order_status' => Order::STATUS_PICKING, 'comments' => '']
                    );

                    $order  =   Order::find($order_id);

                    $rider      =   User::where('id',$user_id)->first();
                    $customer   =   User::find($order->customer_id);
                    if($customer->lang=='en'){
                        $customer_body  =   "Your Booking Order has been assigned to"." ".$rider->first_name ." ".$rider->last_name;
                    }else{
                        $customer_body  =   "Ihr Buchungsauftrag wurde zugewiesen an"." ".$rider->first_name ." ".$rider->last_name;
                    }

                    FireBaseMessaging::send_notification($rider->fcm_token,"Admin Assigned you a ride","Assigned Order");
                    FireBaseMessaging::send_notification($customer->fcm_token,$customer_body,"Assigned Order");

                    FireBaseMessaging::send_notification($customer->fcm_web_token,$customer_body,"Assigned Order");
                    FireBaseMessaging::send_notification($rider->fcm_web_token,"Admin Assigned you a ride","Assigned Order");

                    Notification::create([
                        'user_id'  =>  $user_id,
                        'user_to_notify'  => $order->customer_id,
                        'notifications_text'  =>  "Admin Assigned you a rider: " ." Rider id :".$rider->id. " Order id : " .$order_id,
                    ]);

                    Notification::create([
                        'user_id'  =>  $user_id,
                        'user_to_notify'  => $rider->id,
                        'notifications_text'  =>  "Admin Assigned you a ride: "." Order id :" .$order_id,
                    ]);

                    $adminUsers = User::where('role_id', 1)->get();
                    foreach ($adminUsers as $single) {
                        if($single)
                            Notification::create([
                                'user_id'  =>  $user_id,
                                'user_to_notify'  => $single->id,
                                'notifications_text'  => "Admin Assigned a ride Rider id: ".$rider->id." Order id :" .$order_id,
                            ]);
                    }

                    SendAssignRider::dispatch(
                        $customer,
                        new AssignRider(
                            $customer,
                            $order->id,
                            $rider,
                            $order->total_meter,
                            $order->total_amount,
                            "pending",
                            $order->order_status
                        ),
                        $rider,
                        $order->total_meter,
                        $order->total_amount,
                        "pending",
                        $order->order_status
                    );
                    SendAssignRider::dispatch($rider,
                        new AssignRider(
                            $rider,
                            $order->id,
                            $customer,
                            $order->total_meter,
                            $order->total_amount,
                            "pending",
                            $order->order_status
                        ),
                        $customer,
                        $order->total_meter,
                        $order->total_amount,
                        "pending",
                        $order->order_status
                    );
                    $order_assign->update([
                        'assign_status' =>  'assign'
                    ]);
                    return response()->json([
                        'status'       =>  true,
                        'messages'      =>  'Order successfully assign to rider.'
                    ],200);
                }
            }
        }
        if (!empty($request['status']) && $request['status'] === 'rejected'){
            $order          =   Order::find($order_id);
            if ($order_assign) {
                $order_assign->update([
                    'assign_status' =>  'rejected'
                ]);
            }
            if ($order){
                $order->forceFill([
                    'is_assign'     =>  0
                ])->save();
            }
            return response()->json([
                'status'       =>  true,
                'messages'      =>  'Order assign Rejected'
            ],200);
        }
        return response()->json([
            'status'       =>  false,
            'messages'      =>  'Invalid Order'
        ],400);
    }

    /**
     * Upload a file and attach it to an existing order belonging to the authenticated customer.
     */
    public function sendFileToRider(SendFileToRiderRequest $request)
    {
        $order = Order::where('id', $request->order_id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        if ($order->image_name) {
            Storage::disk('public')->delete($order->image_name);
        }

        $path = $request->file('image_name')->store('order-files', 'public');

        $order->update(['image_name' => $path]);

        return response()->json([
            'success'  => true,
            'messages' => 'File uploaded.',
        ], 200);
    }
}
