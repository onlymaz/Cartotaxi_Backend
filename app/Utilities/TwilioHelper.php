<?php


namespace App\Utilities;


use App\Helpers\TwilioApi;

class TwilioHelper
{
    public static function SendSMS($phone_number,$message)
    {
        try{
            $twilio = new TwilioApi(env('TWILIO_ACCOUNT_SID'),env('TWILIO_AUTH_TOKEN'));
            $message  = $twilio->messages->create(
                $phone_number,
                array(
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    'body' => $message
                )
            );
        }
        catch (\Exception $e){

        }
    }

}
