<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\SettingRequest;
use App\Models\SiteSetting;
use App\Traits\UploadImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\Models\Order;
use Barryvdh\DomPDF\Facade as PDF;

class SettingController extends Controller
{
    use UploadImage;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $setting    =   SiteSetting::find(1);
        return view('admin.settings.index',compact('setting'));
    }
     public function deleteUsere($id){
        $user = User::where('id',$id)->first();
        if($user){
         User::where('id',$user->id)->update([
                'email'=>   "rand".mt_rand(100000, 999999)."@anyx.com",
                'first_name'=>  "",
                'last_name'=>  "",
                'phone_number'=>  "",
                'fcm_token'=>  "",
                'fcm_web_token'=>  "",
                'access_token'=>  "",
                'isActive'=>  "0",
               ]);
        }
        Auth::logout();
         return redirect()->back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SettingRequest $request, $id)
    {
        $setting    =   SiteSetting::find(1);
        $site_logo  =   $setting->site_logo;
        $site_icon  =   $setting->site_icon;
        if ($request->site_logo){
            if ($setting->site_logo){
                $this->deleteOne($setting->site_logo);
            }
            $site_logo = $this->uploadImage($request->site_logo);
        }
        if ($request->site_icon){
            if ($setting->site_icon){
                $this->deleteOne($setting->site_icon);
            }
            $site_icon = $this->uploadImage($request->site_icon);
        }
        $updateData = [
            'site_name' =>  $request->site_name ?? $setting->site_name,
            'playstore_link' =>  $request->playstore_link ?? $setting->playstore_link,
            'appstore_link' =>  $request->appstore_link ?? $setting->appstore_link,
            'contact_number' =>  $request->contact_number ?? $setting->contact_number,
            'contact_email' =>  $request->contact_email ?? $setting->contact_email,
            'google_map_key' =>  $request->google_map_key ?? $setting->google_map_key,
            'social_login' =>  $request->social_login ?? $setting->social_login,
            'site_logo' =>  $site_logo,
            'site_icon' =>  $site_icon,
        ];
        
        // Only update these if they exist in request
        if($request->has('provider_accept_timeout')) {
            $updateData['provider_accept_timeout'] = $request->provider_accept_timeout;
        }
        if($request->has('provider_search_radius')) {
            $updateData['provider_search_radius'] = $request->provider_search_radius;
        }
        if($request->has('sos_number')) {
            $updateData['sos_number'] = $request->sos_number;
        }
        if($request->has('help_content')) {
            $updateData['help_content'] = $request->help_content;
        }
        
        $setting->update($updateData);
        return response()->json([
            'success'    =>  true
        ]);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function profile_view($id)
    {
        $user   =   User::find($id);
        return view('admin.settings.profile-view',compact('user'));
    }

    public function change_password($id)
    {
        $user   =   User::find($id);
        return view('admin.settings.change-password',compact('user'));
    }
    public function profile_update(ProfileRequest $request,$id)
    {
        $user   =   User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $image  =   $user->profile_image;
        if ($request->profile_image){
            if ($user->profile_image){
                $this->deleteOne($user->profile_image);
            }
            $image = $this->uploadImage($request->profile_image);
        }
        $password   =   $user->password;
        if ($request->password){
        if (!Hash::check($request->oldpassword, Auth::user()->password)) {     
                return response()->json([
                    'success'   =>  false
                ]);
            }
            $password   =   Hash::make($request->password);
        }
        
        $user->update([
            'first_name'   =>  !empty($request->first_name)?$request->first_name:$user->first_name,
            'last_name'   =>  !empty($request->last_name)?$request->last_name:$user->last_name,
            'phone_number'   =>  !empty($request->phone_number)?$request->phone_number:$user->phone_number,
            'email'   =>  !empty($request->email)?$request->email:$user->email,
            'profile_image'    =>   $image,
            'password'    =>   $password,
            'bio'         =>   $request->bio,
            'social_links'=>   $request->social_links,
            'company_name'=>   $request->company_name,
            'vat_number'  =>   $request->vat_number,
            'company_address'=> $request->company_address,
            'company_website'=> $request->company_website,
        ]);

        return response()->json([
            'success'   =>  true
        ]);
    }
    public function downloadProfile($id){
        $user = User::where('id',$id)->first();
        $order=Order::with('orderSubTrip','helperOrder.helper','package')->where('customer_id',$user->id)->get();
        return view('download.pdf',compact('order','id'));
    }
    public function createPDF($id) {
        $user = User::where('id',$id)->first();
        $order=Order::with('orderSubTrip','helperOrder.helper','package')->where('customer_id',$user->id)->get();
        $pdf =PDF::loadView('download.pdf',compact('order'))->setPaper('a4', 'landscape');
      return $pdf->download('pdf_file.pdf');
    }
}
