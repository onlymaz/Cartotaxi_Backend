<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewRating extends Model
{
    protected $fillable =   ['user_id','order_id','review_id','review_type_id','rating','comments'];

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id', 'id');
    }

    public function reviewType()
    {
        return $this->belongsTo(ReviewType::class, 'review_type_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
