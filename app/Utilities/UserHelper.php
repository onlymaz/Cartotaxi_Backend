<?php


namespace App\Utilities;


use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;

class UserHelper
{

    public static function dashboard_stats($user)
    {
        if ($user->role_id==2){
            $data['total_bookings'] =   Order::where('rider_id',$user->id)->count();
            $data['scheduled_bookings'] =   Order::where('rider_id',$user->id)
                ->whereDate('picked_time','>',Carbon::today())
                ->count();
            $data['cancelled_bookings'] =   Order::where('rider_id',$user->id)
                ->where('order_status','cancel')
                ->count();
        }else{
            $data['total_bookings'] =   Order::where('customer_id',$user->id)->count();
            $data['scheduled_bookings'] =   Order::where('customer_id',$user->id)
                ->whereDate('picked_time','>',Carbon::today())
                ->count();
            $data['cancelled_bookings'] =   Order::where('customer_id',$user->id)
                ->where('order_status','cancel')
                ->count();
        }

        $data['total_paid']     =   Payment::where('customer_id',$user->id)
                                    ->where('status','completed')
                                    ->sum('amount');

        $data['completed_bookings'] =   Order::where('rider_id',$user->id)
            ->where('order_status','delivered')
            ->count();
        return $data;

    }
    public static function user_stats($user,$type='object')
    {
        $data                   =   UserHelper::dashboard_stats($user);
        $user['profile_image']  =   !empty($user->profile_image)?url($user->profile_image):'';
        $user['access_token']  =   !empty($user->access_token)?$user->access_token:'';
        if (method_exists($user, 'makeVisible')) {
            $user->makeVisible(['access_token']);
        }
        $user['total_bookings']  =   $data['total_bookings'];
        $user['total_paid']  =   $data['total_paid'];
        $user['scheduled_bookings']  =   $data['scheduled_bookings'];
        $user['cancelled_bookings']  =   $data['cancelled_bookings'];
        $user['completed_bookings']  =   $data['completed_bookings'];

        if ($type=='array'){
            return (array)$user;
        }
        return $user;
    }

    public static function user_array($user)
    {
        $data   =   [
            'role_id'   =>  $user->role_id,
            'first_name'   =>  $user->first_name,
            'last_name'   =>  $user->last_name,
            'email'   =>  $user->email,
            'device'   =>  !empty($user->device)?$user->device:'',
            'provider'   =>  !empty($user->provider)?$user->provider:'',
            'phone_number'   =>  $user->phone_number,
            // confirmation_code intentionally excluded — must never be synced to Firebase
            'confirmed'   =>  $user->confirmed,
            'IsActive'   =>  $user->IsActive,
            'fcm_token'   =>  $user->fcm_token,
            'lat'   =>  !empty($user->lat)?$user->lat:'',
            'id'   =>  $user->id,
            'long'   =>  !empty($user->long)?$user->long:'',
            'profile_image'   =>  $user->profile_image,
            'access_token'   =>  $user->access_token,
            'updated_at'   =>  $user->updated_at,
            'created_at'   =>  $user->created_at,
        ];

        return $data;
    }
    public static function orderStatus($order_status){
        switch ($order_status) {
            case 'picking':
                $messages="Started";
                break;
            case 'picked_up':
                $messages="Picked Up";
                break;
            case 'on_way':
                $messages="On the way towards";
                break;
            case 'delivered':
                $messages="Delivered";
                break;
            case 'cancel':
                $messages="Cancelled";
                break;
            case 'not_received':
                $messages="Not Received";
                break;
            case 'accident':
                $messages="Accident";
                break;
            default:
                $messages=$order_status;
                break;
        }
    return $messages;
    }

}
