<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Helper extends Model
{
	protected $fillable  =   ['user_id','total_helper','payment_method','price','address','start_time','end_time'];
	
	public static function getOptionsTimes($time){
		$range=range(strtotime(date($time)),strtotime("23:59"),15*60);
    	return $range;
	}   
    public function helperOrder()
    {
        return $this->hasOne(HelperOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'payment_method');
    }
}
