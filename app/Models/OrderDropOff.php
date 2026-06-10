<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDropOff extends Model
{
    protected $fillable = [
        'order_id',
        'location',
        'is_end_drop_off',
        'status',
        'lat',
        'lng',
    ];
}
