<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityDistrict extends Model
{
    protected $fillable =   ['IsActive','district_name','polygons','city_id'];
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
