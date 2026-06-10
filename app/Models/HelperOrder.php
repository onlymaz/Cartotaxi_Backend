<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelperOrder extends Model
{

    protected $table='pivot_helper_order';
	protected $fillable  =   ['order_id','helper_id'];

    public function helper(){
        return $this->hasOne(Helper::class, 'id','helper_id');
    }
    public static function helperList($id){
    	$helperOrder= HelperOrder::select('total_helper as no_of_helper','end_time as total_hours','price as helper_charges')->join('helpers', 'helpers.id', '=', 'pivot_helper_order.helper_id')
    	->where('order_id',$id)->first();
        if($helperOrder){
            return $helperOrder;
        }else{
            return null;
        }
    }
    public static function helperStatus($id){

    	$status= HelperOrder::where('order_id',$id)->first();
    	if($status){
    		return true;
    	}else{
    		return false;
    	} 
    }

}
