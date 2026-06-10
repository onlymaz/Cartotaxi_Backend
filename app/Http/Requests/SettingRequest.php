<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'site_name' =>  'nullable|string|max:255',
            'playstore_link' =>  'nullable|url',
            'appstore_link' =>  'nullable|url',
            'provider_accept_timeout' =>  'nullable|integer',
            'provider_search_radius' =>  'nullable|integer',
            'contact_number' =>  'nullable|string|max:50',
            'contact_email' =>  'nullable|email|max:255',
            'google_map_key' =>  'nullable|string|max:255',
            'sos_number' =>  'nullable|string|max:50',
            'help_content' =>  'nullable|string|max:500',
            'social_login' =>  'nullable|in:0,1',
            'site_logo' =>  'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_icon' =>  'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
        ];
    }
}
