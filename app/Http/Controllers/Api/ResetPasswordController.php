<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendResetPasswordEmail;
use App\Mail\ResetPasswordEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function create(Request $request)
    {
        $validation_fields  =   [
            'email'        => 'required|email',
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields);
        if($validator->fails()) {
            $messages   =   [];
            foreach ($validator->messages()->getMessages() as $key =>   $message){
                $messages[]     =  $message[0];
            }

            $messages =   implode(" ",$messages);
            return response()->json([
                'status'     =>  false,
                'messages'   =>  $messages
            ], 200);
        }
        // Generic response in both branches to prevent user enumeration and to
        // avoid leaking the reset code in the JSON body (P0).
        $genericResponse = [
            'status'   => true,
            'messages' => 'If the email exists, a reset code has been sent.',
        ];

        $user = User::where('email', '=', $request['email'])->first();
        if ($user) {
            // Cryptographically strong code (random_int) + short expiry; hash before
            // persisting so DB read does not expose a usable reset code.
            $rawCode = (string) random_int(100000, 999999);

            $user->forget_code            = Hash::make($rawCode);
            $user->forget_code_expires_at = now()->addMinutes(15);
            $user->save();

            // Pass the raw code into the mailable; never persist or return it.
            $this->dispatch(new SendResetPasswordEmail($user, $rawCode));
        }

        return response()->json($genericResponse, 200);
    }

    public function store(Request $request)
    {
        $validation_fields  =   [
            'email'        => 'required|email',
            'code'         => 'required',
            'password'      => ['required', 'string', 'min:6', 'confirmed'],
        ];
        //,'regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,120}$/'
        $customMessages = [
            'regex' => 'Please follow the given password pattern: minimum characters 6, atleast 1 capital, atleast 1 small'
        ];
        $validator     =  $this->getValidationFactory()->make($request->all(),$validation_fields,$customMessages);
        if($validator->fails()) {
            $messages   =   [];
            foreach ($validator->messages()->getMessages() as $key =>   $message){
                $messages[]     =  $message[0];
            }
            $messages =   implode(" ",$messages);
            return response()->json([
                'status'     =>  false,
                'messages'   =>  $messages
            ], 200);
        }
        $user = User::where('email', '=', $request['email'])->first();

        // Reject fast on missing user, expired code, missing/used code, or hash mismatch.
        // Generic message in every failure branch to avoid leaking which check failed.
        $invalid = static function () {
            return response()->json([
                'status'   => false,
                'messages' => 'Invalid or expired code.',
            ], 200);
        };

        if (!$user || empty($user->forget_code)) {
            return $invalid();
        }
        if ($user->forget_code_expires_at && now()->greaterThan($user->forget_code_expires_at)) {
            return $invalid();
        }
        if (!Hash::check((string) $request['code'], $user->forget_code)) {
            return $invalid();
        }

        // Single-use: clear the stored hash + expiry once consumed.
        $user->password               = Hash::make($request['password']);
        $user->forget_code            = null;
        $user->forget_code_expires_at = null;
        $user->access_token           = ''; // invalidate any active mobile session
        $user->save();

        return response()->json([
            'status'   => true,
            'messages' => 'Password Changed Successfully',
        ], 200);
    }
}
