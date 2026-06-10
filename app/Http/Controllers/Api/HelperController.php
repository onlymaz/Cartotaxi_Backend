<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use App\Traits\UploadImage;
use App\Utilities\FireBaseMessaging;
use App\Mail\StoreBooking;
use App\Jobs\CreateBooking;
use App\Mail\AssignRider;
use App\Jobs\SendAssignRider;
use App\Models\Helper;



class HelperController extends Controller
{
    public function show(Request $request)
    {
        $data=[];
        $user    =   $request->user();
        $helper_list= Helper::with('helperOrder')->where('user_id',$user->id)->orderby('id','desc')->get();
        foreach ($helper_list as $key) {
            $data[]=[
                "helper_id"=> $key->id, 
                "user_id"=> $key->user_id, 
                "total_helper"=> $key->total_helper, 
                "payment_method"=> $key->payment_method, 
                "status"=> ($key->status==0)? "Pending": "Confirm", 
                "helper_cost"=> $key->price, 
                "address"=> $key->address, 
                "start_time"=> $key->start_time, 
                "hours"=> $key->end_time, 
                "booking_id"=>($key->helperOrder) ? $key->helperOrder->order_id :""
            ];
        }
        if(!$data){
            return response()->json([
                'status'       => true,
                'messages'      => "Helper List Record not found",
            ]);
        }
            return response()->json([
                'status'       => true,
                'messages'      => "Helper List",
                'data'          => $data
            ]);
    }
    public function create(Request $request)
    {
        $user    =   $request->user();
        $validation_fields  =   [
            'total_helper'    =>  'required',
            'payment_method'  =>  'required',
            'price'           =>  'required',
            'address'         =>  'required',
            'start_time'      =>  'required',
            'hours'           =>  'required',
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
        $data=[
            'user_id'=>$user->id,
            'total_helper'=>$request->total_helper,
            'payment_method'=>$request->payment_method,
            'price'=>$request->price,
            'address'=>$request->address,
            'start_time'=>$request->start_time,
            'end_time'=>$request->hours,
            'created_at'=>$current_time = Carbon::now()->toDateTimeString(),
            'updated_at'=>$current_time = Carbon::now()->toDateTimeString(),
        ];

        Helper::insert($data);
        return response()->json([
                'status'       => true,
                'messages'      => "Helper Created Successfully",
            ]);


    }

}
