<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAssign extends Model
{
    protected $fillable = [
        'order_id', 'rider_id', 'assign_status',
    ];
}
