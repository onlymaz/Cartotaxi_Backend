<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSubTrip extends Model
{
    protected $fillable =   ['order_id','start_district_id','end_district_id','start_location','end_location','start_lat','start_long',
                            'end_lat','end_long','total_amount','total_meter','total_second','status'];


    public static function locationObject($id){
    	return OrderSubTrip::select('id','order_id','start_district_id','end_district_id','start_location','end_location','start_lat','start_long','end_lat','end_long','status')->where('order_id',$id)->get();
    }
    public static  function totalOrderMetter($id){
        	return number_format((float)OrderSubTrip::where('order_id',$id)->sum('total_meter')/1000, 3, '.', '');
    }
    public static function totalCost($id){
        	return number_format((float)OrderSubTrip::where('order_id',$id)->sum('total_amount'), 2, '.', '');
    }
}
