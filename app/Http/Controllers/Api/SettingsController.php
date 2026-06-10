<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CityDistrict;
use App\Models\Package;
use App\Traits\UploadImage;
use App\Utilities\FireBaseRealTimeDatabase;
use App\Utilities\UserHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\HelperFee;

class SettingsController extends Controller
{
    use UploadImage;
    public function AllowedArea(Request $request)
    {
        $data = [
            'polygons' => [],
            'polygon_data' => [],
            'packages' => [],
        ];
        $districts  =   CityDistrict::where('IsActive',1)->get();
        $packages   =   Package::select('id','name','weight','per_km_charges','fixed_price')->get();

        if (!empty($districts)){

            foreach ($districts as $district)
            {
                $polygons   =   json_decode($district->polygons);
                if (!empty($polygons)){
                    foreach ($polygons as $polygon)
                    {
                        $array[]    = [
                            'lat'   =>  $polygon->lat,
                            'lng'   =>  $polygon->lng,
                            'id'   =>  $district->id,
                        ];
                    }

                    $data['polygons']   = $array;
                    $data['polygon_data'][] = [
                        'id' => $district->id,
                        'minus_district_id' => 0,
                        'polygon' => $district->polygons,
                    ];
                }else{
                    $data['polygons']   =   [];
                }

            }
        }else{
            $data['polygons']   =   [];
        }
        if (!empty($packages)){
            foreach ($packages as $package)
            {
                $data['packages'][]    = [
                    'id'   =>  $package->id,
                    'name'   =>  $package->name,
                    'weight'   =>  $package->weight,
                    'per_km_charges'   =>  $package->per_km_charges,
                    'fixed_price'   =>  $package->fixed_price,
                ];
            }
        }else{
            $data['packages']  =   [];
        }
        // Hoisted out of the loop (was an N+1 query) and made null-safe so an
        // empty helper_fees table doesn't 500 the endpoint.
        $data['helper_fee'] = optional(HelperFee::first())->fee ?? 0;

        return response()->json([
            'status'    => true,
            'messages'  =>  'Areas',
            'data'      =>  $data
        ]);
    }
    public function AllowedAreaIos(Request $request){
        $data = [
            'polygons' => [],
            'polygon_data' => [],
            'Vienna' => [],
            'packages' => [],
        ];
        $districts  =   CityDistrict::where('IsActive',1)->where('district_name','!=','Vienna')->get();
        $packages   =   Package::select('id','name','weight','per_km_charges','fixed_price')->get();
        $Vienna   =     CityDistrict::where('IsActive',1)->where('district_name','=','Vienna')->get();
        if (!empty($districts)){

            foreach ($districts as $district)
            {
                $polygons   =   json_decode($district->polygons);
                if (!empty($polygons)){
                    foreach ($polygons as $polygon)
                    {
                        $array[]    = [
                            'lat'   =>  $polygon->lat,
                            'lng'   =>  $polygon->lng,
                            'id'   =>  $district->id,
                        ];
                    }
                    $data['polygons']   = $array;
                    $data['polygon_data'][] = [
                        'id' => $district->id,
                        'minus_district_id' => 0,
                        'polygon' => $district->polygons,
                    ];
                }else{
                    $data['polygons']   =   [];
                }
            }
        }else{
            $data['polygons']   =   [];
        }


        if (!empty($Vienna)){
            foreach ($Vienna as $districtv)
            {
                $polygonsv   =   json_decode($districtv->polygons);
                if (!empty($polygonsv)){
                    foreach ($polygonsv as $polygonv)
                    {
                        $arrayv[]    = [
                            'lat'   =>  $polygonv->lat,
                            'lng'   =>  $polygonv->lng,
                             'id'   =>  $districtv->id,
                        ];
                    }
                    $data['Vienna']   = $arrayv;
                }else{
                    $data['Vienna']   =   [];
                }
            }
        }else{
            $data['Vienna']   =   [];
        }








        if (!empty($packages)){
            foreach ($packages as $package)
            {
                $data['packages'][]    = [
                    'id'   =>  $package->id,
                    'name'   =>  $package->name,
                    'weight'   =>  $package->weight,
                    'per_km_charges'   =>  $package->per_km_charges,
                    'fixed_price'   =>  $package->fixed_price,
                ];
            }
        }else{
            $data['packages']  =   [];
        }

        return response()->json([
            'status'    => true,
            'messages'  =>  'Areas',
            'data'      =>  $data
        ]);
    }

    public function profile_update(Request $request)
    {
        $validation_fields  =   [
            'first_name'        => 'required',
            'last_name'         => 'required',
            'password'      => ['nullable', 'min:6', 'confirmed'],
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


        $user   =   $request->user();
        $image  =   $user->profile_image;
        if ($request->profile_image){
            if ($user->profile_image){
                $this->deleteOne($user->profile_image);
            }
            $image  =   $this->uploadImage($request->profile_image);
        }
        $user = $request->user();
        if($request->oldpassword){

        if (!Hash::check($request->oldpassword, $user->password)) {
            return response()->json([
                    'success'   =>  false,
                    'messages' => 'Old Password did not match'
                ]);
            }
        }
        $user->update([
            'first_name'    =>  $request->first_name,
            'last_name'    =>  $request->last_name,
            'profile_image'    =>  $image,
            'password'    =>  !empty($request->password)?Hash::make($request->password):$user->password,
        ]);
        $data_user['user']  =   UserHelper::user_stats($user);
        $object         =   UserHelper::user_array($user);
        $reference  =   'users/'.$user->id;
        FireBaseRealTimeDatabase::StoreData($reference,$object);
        return  response()->json([
            'status'    => true,
            'messages'  => 'User Updated Successfully',
            'data'      => $data_user
        ], 200);
    }
    public function HelperDetail()
    {
        // Null-safe: previously this 500'd with "trying to read property 'fee'
        // on null" whenever the helper_fees table was empty.
        $data = optional(HelperFee::first())->fee ?? 0;

        return response()->json([
            'status'    => true,
            'messages'  => 'helper fee',
            'data'      => $data,
        ]);
    }
}
