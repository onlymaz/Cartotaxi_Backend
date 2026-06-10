<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable =   ['site_name','site_logo','site_icon','playstore_link','appstore_link','provider_accept_timeout','provider_search_radius',
        'sos_number','contact_number','contact_email','help_content','google_map_key','social_login'];
}
