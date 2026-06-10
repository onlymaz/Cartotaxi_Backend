<?php

namespace App\Models;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table="notifications";
    protected $fillable=['user_id','user_to_notify','notifications_text','read'];

    public function user(){
        return $this->hasOne(User::class, 'id','user_to_notify');
    }
}
