<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\User;
use Auth;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.ct-login');
    }
    public function verifyCode(Request $request){
        if (!$request->filled('email') || !$request->filled('code')) {
            $message = "Email and code are required";
            return view('auth.verify', compact('message'));
        }
        $user = User::where('email', $request->email)->first();

        // Single generic message for every failure path — prevents enumeration of
        // both registered emails and code values.
        $invalidMessage = "Email or code is incorrect";

        if (!$user || empty($user->confirmation_code)) {
            return view('auth.verify', ['message' => $invalidMessage]);
        }
        if ($user->confirmation_code_expires_at && now()->greaterThan($user->confirmation_code_expires_at)) {
            return view('auth.verify', ['message' => $invalidMessage]);
        }
        if (!\Illuminate\Support\Facades\Hash::check((string) $request->code, $user->confirmation_code)) {
            return view('auth.verify', ['message' => $invalidMessage]);
        }

        $user->confirmed                    = 1;
        $user->confirmation_code            = null;
        $user->confirmation_code_expires_at = null;
        $user->save();

        $this->guard()->login($user);

        return redirect('login');
    }
    public function logout(Request $request){
        $user = $request->user();
        if ($user) {
            $user->fcm_token = '';
            $user->access_token = '';
            $user->fcm_web_token = '';
            $user->save();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function login(Request $request){
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            ]);
        $user = User::where('email',$request->email)->first();
        if($user){
            if($user->role_id!=1){
                if($user->confirmed==0){
                    return redirect('/verify/code');
                }
            }
            if (\Auth::attempt([
                'email' => $request->email,
                'password' => $request->password])
            ){
                return redirect()->back();
            }
        }

        return redirect('/login')->with('message', 'Invalid Email address or Password');
    }
   
}
