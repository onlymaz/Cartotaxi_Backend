<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable =   ['order_id','customer_id','gateway_id','amount','transactions','response','status','transaction_id'];

    public function gateway(){
        return $this->hasOne(Gateway::class,'id','gateway_id');
    }
}
