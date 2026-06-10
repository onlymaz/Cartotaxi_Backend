<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderSubTrip;
use App\Models\OrderDropOff;
use App\Models\User;
use Illuminate\Http\Request;
use App\Utilities\FireBaseMessaging;
class RiderRouteController extends Controller
{
    public function start_rider(Request $request){
        $validation_fields  =   [
            'order_id'          =>  'required|numeric',
            'rider_id'          =>  'required|sometimes'
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields);
        if($validator->fails()) {
            $messages   =   [];
            foreach ($validator->messages()->getMessages() as $key =>   $message){
                $messages[]  =
                    $message[0];
            }
            $messages =   implode(" ",$messages);
            return response()->json([
                'status'     =>  false,
                'messages'   =>  $messages
            ], 200);
        }
        $order_id           =   $request['order_id'];
        $user               =   $request->user();
        $rider_id           =   !empty($request['rider_id'])?$request['rider_id']:$user->id;

        $orders             =   Order::where('rider_id',$rider_id)
            ->where('id',$order_id)->first();
       /* if(!empty($request['rider_id'])){
            $orders             =   Order::where('rider_id',$request['rider_id'])
                ->where('id',$order_id)->first();
        }
        else{
            $orders             =   Order::where('rider_id',$user->id)
                ->where('id',$order_id)->first();
        }*/

        if ($orders){
            $order              =   OrderDropOff::where('order_id',$order_id)
                ->where('status','=','pending')
                ->orderBy('id','asc')
                ->first();
            if ($order){
                /*$order_trip              =   Order::find($order_id);
                $customer_id        =   $order_trip->customer_id;
                $customer           =   User::find($customer_id);
                FireBaseMessaging::send_notification_custom($customer->fcm_token,'Order Status',"Your order is start from : ".$order->start_location,'deliver_start');*/

                return response()->json([
                    'status'        =>  true,
                    'messages'      =>  'order loaded',
                    'is_end_drop_off'=>  $order->is_end_drop_off,
                    'data'          =>  [
                        'drop_off_id'        =>   $order->id,
                        'location'           =>   $order->location,
                        'lat'                =>   $order->lat,
                        'lng'                =>   $order->lng
                    ]
                ],200);
            }
            return response()->json([
                'status'       =>  true,
                'messages'      =>  'order loaded',
                'data'          =>  new \stdClass()
            ],200);
        }
        return response()->json([
            'status'       =>  false,
            'user_id'       =>  $user->id,
            'messages'      =>  'order is not belong to rider',
            'data'          =>  []
        ],200);
    }

    public function ride_completed(Request $request){
        $validation_fields  =   [
            'drop_off_id'          =>  'required|numeric'
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields);
        if($validator->fails()) {
            $messages   =   [];
            foreach ($validator->messages()->getMessages() as $key =>   $message){
                $messages[]  =
                    $message[0];
            }
            $messages =   implode(" ",$messages);
            return response()->json([
                'status'     =>  false,
                'messages'   =>  $messages
            ], 200);
        }
        $order_trip_id           =   $request['drop_off_id'];
        $order_trip              =   OrderDropOff::find($order_trip_id);
        if ($order_trip){
            $order_id            =  $order_trip->order_id;
            $order_trip1         = $order_trip;

            /*$order              =   Order::find($order_id);
            $customer_id        =   $order->customer_id;
            $customer           =   User::find($customer_id);
            FireBaseMessaging::send_notification_custom($customer->fcm_token,'Order Status',"Your order is reached from : ".$order_trip->end_location,'deliver_completed');*/
            $order_trip->update([
                'status'        =>  'completed'
            ]);
            $order               =  Order::find($order_id);

            if ($order_trip1->is_end_drop_off ===1){
                return response()->json([
                    'status'    =>  true,
                    'messages'  =>  'last drop Off',
                    'is_end_drop_off'=>  $order_trip1->is_end_drop_off,
                ],200);
            }
            return response()->json([
                'status'    =>  true,
                'messages'  =>  'drop off completed now',
                'is_end_drop_off'=>  $order_trip1->is_end_drop_off,
            ],200);
        }
    }
}
