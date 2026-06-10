<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendConfirmationEmail;
use App\Models\Order;
use App\Models\User;
use App\Utilities\FireBaseRealTimeDatabase;
use App\Utilities\UserHelper;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validation_fields = [
            'email'        => 'required|email|unique:users',
            'first_name'   => 'required',
            'last_name'    => 'required',
            'phone_number' => 'required|unique:users',
            'password'     => ['required', 'min:6', 'confirmed'],
            'lat'          => 'required|numeric|between:-90,90',
            'long'         => 'required|numeric|between:-180,180',
        ];

        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $rawCode = (string) random_int(100000, 999999);

        // role_id and confirmed are intentionally NOT in $fillable. forceCreate
        // bypasses mass-assignment guards — used here because the role and
        // initial verification state are server-controlled, not client input.
        $user = User::forceCreate([
            'role_id'                       => 3,
            'first_name'                    => ucfirst($request['first_name']),
            'last_name'                     => ucfirst($request['last_name']),
            'email'                         => $request['email'],
            'device'                        => $request['device'],
            'provider'                      => 'app',
            'phone_number'                  => $request['phone_number'],
            'password'                      => Hash::make($request['password']),
            'confirmation_code'             => Hash::make($rawCode),
            'confirmation_code_expires_at'  => now()->addMinutes(30),
            'confirmed'                     => 0,
            'IsActive'                      => 0,
            'fcm_token'                     => '',
            'lat'                           => $request->lat,
            'long'                          => $request->long,
        ]);

        $data['user'] = UserHelper::user_stats($user);
        $object       = UserHelper::user_array($user);
        $this->dispatch(new SendConfirmationEmail($user, $rawCode));
        $reference = 'users/' . $user->id;
        FireBaseRealTimeDatabase::StoreData($reference, $object);

        return response()->json([
            'status'   => true,
            'messages' => 'Successfully created account',
            'data'     => $data
        ], 201);
    }

    public function Login(Request $request)
    {
        $validation_fields = [
            'email'     => 'required|email',
            'password'  => 'required',
            'fcm_token' => 'required',
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $key  = config('app.jwt_secret');
        $user = User::where('email', $request->email)->first();

        // Admin accounts cannot log in via the mobile API
        if (isset($user) && $user->role_id == 1) {
            return response()->json([
                'status'   => false,
                'messages' => 'Admin accounts cannot use the mobile API.',
            ], 403);
        }

        if (!empty($user) && !$user->confirmed) {
            $data_user['user'] = UserHelper::user_stats($user);
            return response()->json([
                'status'   => true,
                'messages' => 'Please confirm your account first',
                'data'     => $data_user
            ], 200);
        }

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user  = User::where('email', '=', $request->email)->first();
            $now   = now()->timestamp;
            $token = JWT::encode([
                'iss' => config('app.url'),
                'sub' => $user->id,
                'iat' => $now,
                'nbf' => $now,
                'exp' => $now + (int) config('app.jwt_ttl', 86400),
                'jti' => (string) Str::uuid(),
                'email' => $user->email,
                'id'    => $user->id,
            ], $key, 'HS256');
            // access_token is not in $fillable; assign directly so mass-assignment
            // can never set it from request input.
            $user->access_token = $token;
            $user->fcm_token    = $request->fcm_token;
            $user->save();
            $data_user['user'] = UserHelper::user_stats($user);
            $object            = UserHelper::user_array($user);
            $reference         = 'users/' . $user->id;
            FireBaseRealTimeDatabase::StoreData($reference, $object);

            return response()->json([
                'status'   => true,
                'messages' => 'Logged in Successfully',
                'data'     => $data_user
            ], 200);
        }

        return response()->json([
            'status'   => false,
            'messages' => 'Incorrect Credentials'
        ], 401);
    }

    public function update_fcm_token(Request $request)
    {
        $validation_fields = [
            'fcm_token' => 'required',
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $user = User::where('email', '=', $request->user()->email)->first();
        $user->update(['fcm_token' => $request->fcm_token]);

        $data_user['user'] = UserHelper::user_stats($user);
        $object            = UserHelper::user_array($user);
        $reference         = 'users/' . $user->id;
        FireBaseRealTimeDatabase::StoreData($reference, $object);

        return response()->json([
            'status'   => true,
            'messages' => 'Fcm Token updated Successfully',
            'data'     => $data_user
        ], 200);
    }

    public function codeConfirmed(Request $request)
    {
        $validation_fields = [
            'email'     => 'required|email',
            'code'      => 'required',
            'fcm_token' => 'required',
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $invalidCode = static function () {
            return response()->json([
                'status'   => false,
                'messages' => 'Email does not match with code'
            ], 400);
        };

        if (!$user || empty($user->confirmation_code)) {
            return $invalidCode();
        }
        if ($user->confirmation_code_expires_at && now()->greaterThan($user->confirmation_code_expires_at)) {
            return $invalidCode();
        }
        if (!Hash::check((string) $request->code, $user->confirmation_code)) {
            return $invalidCode();
        }

        $now   = now()->timestamp;
        $token = JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + (int) config('app.jwt_ttl', 86400),
            'jti' => (string) Str::uuid(),
            'email' => $user->email,
            'id'    => $user->id,
        ], config('app.jwt_secret'), 'HS256');

        $user->confirmation_code             = null;
        $user->confirmation_code_expires_at  = null;
        $user->confirmed                     = 1;
        $user->fcm_token                     = $request->fcm_token;
        $user->IsActive                      = 1;
        $user->access_token                  = $token;
        $user->save();

        $data_user['user'] = UserHelper::user_stats($user);
        $object            = UserHelper::user_array($user);
        $reference         = 'users/' . $user->id;
        FireBaseRealTimeDatabase::StoreData($reference, $object);

        return response()->json([
            'status'   => true,
            'messages' => 'success',
            'data'     => $data_user
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->fcm_token     = '';
        $user->access_token  = '';
        $user->fcm_web_token = '';
        $user->save();

        return response()->json([
            'status'   => true,
            'messages' => 'Logged out successfully'
        ]);
    }

    public function DeleteUser(Request $request)
    {
        $user = $request->user();
        if ($user) {
            User::where('id', $user->id)->update([
                'email'         => 'deleted_' . $user->id . '_' . random_int(100000, 999999) . '@deleted.invalid',
                'first_name'    => '',
                'last_name'     => '',
                'phone_number'  => '',
                'fcm_token'     => '',
                'fcm_web_token' => '',
                'access_token'  => '',
                'IsActive'      => '0',
            ]);
        }

        return response()->json([
            'status'   => true,
            'messages' => 'Delete Successfully'
        ]);
    }

    public function ReSendConfirmationCode(Request $request)
    {
        $validation_fields = [
            'email' => 'required|email'
        ];
        $validator = $this->getValidationFactory()->make($request->all(), $validation_fields);
        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0))
            ], 422);
        }

        $user = User::where('email', '=', $request->email)->first();
        if ($user) {
            $rawCode = (string) random_int(100000, 999999);
            $user->touch();
            $user->confirmation_code            = Hash::make($rawCode);
            $user->confirmation_code_expires_at = now()->addMinutes(30);
            $user->save();
            $this->dispatch(new SendConfirmationEmail($user, $rawCode));
        }

        // Generic response in both branches to prevent user enumeration.
        return response()->json([
            'status'   => true,
            'messages' => 'If an account exists for this email, a confirmation code has been sent.',
        ], 200);
    }

    public function statistics(Request $request)
    {
        $user = $request->user();

        if ($user->role_id == 3) {
            $totalPayment   = Payment::where('status', 'completed')->where('customer_id', $user->id)->sum('amount');
            $totalOrder     = Order::where('customer_id', $user->id)->count('id');
            $completedOrder = Order::where('customer_id', $user->id)->where('order_status', 'delivered')->count('id');
            $scheduleOrder  = Order::where('customer_id', $user->id)
                ->whereDate('picked_time', '>', Carbon::today())
                ->count('id');

            $data['total_payment']  = floatval(number_format((float)$totalPayment, 2, '.', ''));
            $data['totalOrder']     = $totalOrder;
            $data['completedOrder'] = $completedOrder;
            $data['scheduleOrder']  = $scheduleOrder;

        } elseif ($user->role_id == 2) {
            $completedRide = Order::where('rider_id', $user->id)->where('order_status', 'delivered')->count('id');
            $assignRide    = Order::where('rider_id', $user->id)
                ->where('order_status', '!=', 'delivered')
                ->count('id');

            $data['completed_ride'] = $completedRide;
            $data['assigned_ride']  = $assignRide;
        } else {
            $data = [];
        }

        return response()->json([
            'status'     => true,
            'messages'   => 'User Statistics',
            'user_stats' => $data
        ]);
    }
}
