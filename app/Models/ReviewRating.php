<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewRating extends Model
{
    protected $fillable =   ['user_id','order_id','review_id','review_type_id','rating','comments'];
}
