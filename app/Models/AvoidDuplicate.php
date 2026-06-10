<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvoidDuplicate extends Model
{
    protected $fillable = [
        'order_id', 'rider_id', 'rating_count', 'start_week', 'end_week',
    ];
}
