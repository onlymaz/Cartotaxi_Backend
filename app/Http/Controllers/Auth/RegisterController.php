<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendConfirmationEmail;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);
    }
    public function findLocation($location)
    {
        \Geocoder::setApiKey(config('services.google.maps_api_key'));
        return \Geocoder::getCoordinatesForAddress($location);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $rawCode = (string) random_int(100000, 999999);

        // role_id is server-controlled; forceCreate bypasses $fillable
        // (which intentionally excludes role_id to prevent privilege escalation
        // via mass assignment from any future caller).
        $user = User::forceCreate([
            'first_name'                   => $data['first_name'],
            'last_name'                    => $data['last_name'],
            'email'                        => $data['email'],
            'password'                     => Hash::make($data['password']),
            'role_id'                      => 3,
            'provider'                     => 'web',
            'device'                       => 'web',
            'IsActive'                     => 1,
            'confirmed'                    => 0,
            'confirmation_code'            => Hash::make($rawCode),
            'confirmation_code_expires_at' => now()->addMinutes(30),
            'lat'                          => null,
            'long'                         => null,
        ]);
        $this->dispatch(new SendConfirmationEmail($user, $rawCode));
        return $user;
    }
}
