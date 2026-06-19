<?php


namespace App\Utilities;


use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\RawMessageFromArray;

class FireBaseMessaging
{
    public static function send_notification($fcm_token,$text,$title,$order="",$name='',$type='',$user_id='',$badge=1,$image='',$sound='default')
    {
        if($order){
            $orders=[
                  'id' => $order->id,
                  // 'booking_id' => $order->booking_id,
                  // 'customer_id' => $order->customer_id,
                  // 'rider_id' => $order->rider_id,
                  // 'start_location' => $order->start_location,
                  // 'end_location' => $order->end_location,
                  // 'picked_time' => $order->picked_time,
                  // 'description' => $order->description,
                  // 'fixed_price' => $order->fixed_price,
                  // 'total_amount' => $order->total_amount,
                  // 'total_meter' => $order->total_meter,
                  // 'total_second' => $order->total_second,
                  // 'per_km_charges' => $order->per_km_charges,
                  // 'start_time' => $order->start_time,
                  // 'end_time' => $order->end_time,
                  'order_status' => $order->order_status,
                  // 'sign' => $order->sign,
            ];
        // \Log::info($order);
        }else
        {
            $orders=[
                'id' => "0",
                'order_status' => "0"
            ];
        }
        $message = new RawMessageFromArray([
            'token' => $fcm_token,
            'notification' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#notification
                'title' => $title,
                'body' => $text,
                'image' => '',
            ],
            'data' => [
                'order_id' => ($order)?(string)$orders['id']:"",
                'order_status' => ($order)?(string)$orders['order_status']:"",
                // The apps route pushes on this (e.g. new_order opens the
                // incoming-ride alert in the Driver app).
                'type' => (string)$type,
            ],
            'android' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
                'ttl' => '3600s',
                'priority' => 'high',
                'notification' => [
                    'title' => $title,
                    'body' => $text,
                    'icon' => '',
                    'color' => '',
                ],
                /*'data'  =>  [

                ],*/

            ],
            'apns' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#apnsconfig
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'subtitle' => '',
                            'body' => $text,
                        ],
                        'order_id' => ($order)?(int)$orders['id']:0,
                        'order_status' => ($order)?(string)$orders['order_status']:"",
                        'type' => (string)$type,
                        /*'badge' => $badge,*/
                        'sound' => $sound,
                        "mutable-content"=> 1,
                    ],
                    'fcm_options' => [
                        // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                        'image' => !empty($image)?$image:url('images/logo.png')
                    ],
                    /*'type' => $type,
                    'name'  =>  $name,
                    'user_id'  =>  $user_id,*/

                ],

            ],
            'webpush' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushconfig
                'notification' => [
                    'title' => $title,
                    'body' => $text,
                    'icon' => url('images/logo.png'),
                ],
            ],
            'fcm_options' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                'analytics_label' => 'some-analytics-label'
            ]
        ]);
        $user   =   User::where('fcm_token',$fcm_token)->first();

        if ($user){
            $array  =   [
                $user->id,
                $user->first_name,
                $text
            ];
        }else{
            $array = [];
        }



        try{

            Log::info('Notification Sent');
            $messaging = app('firebase.messaging');
            $messaging->send($message);


        }
        catch(\Throwable $e) {
            Log::warning('Firebase messaging skipped: '.$e->getMessage(), [
                'recipient' => $array,
                'has_token' => !empty($fcm_token),
            ]);
        }

    }
    public static function sendWebNotificaiton($fcm_token,$title,$body){
        // Migrated off the legacy fcm.googleapis.com/fcm/send endpoint
        // (shut down by Google) to the FCM v1 API via the kreait SDK.
        if (empty($fcm_token)) {
            return;
        }

        $message = new RawMessageFromArray([
            'token' => $fcm_token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'webpush' => [
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'icon'  => url('images/logo.png'),
                ],
            ],
        ]);

        try {
            app('firebase.messaging')->send($message);
        } catch (\Throwable $e) {
            Log::warning('Firebase web push skipped: '.$e->getMessage());
        }
    }
}
