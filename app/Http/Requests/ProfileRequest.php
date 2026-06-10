<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
        $rules  =   [
            'first_name'    =>  'sometimes|required',
            'last_name'    =>  'sometimes|required',
            'email'         => 'sometimes|required|email|unique:users,email,'.\Auth::id(),
            'phone_number'  => 'nullable|unique:users,phone_number,'.\Auth::id(),
            'password'      => 'nullable|string|min:6|confirmed',
            'bio'           => 'nullable|string',
            'social_links'  => 'nullable|string',
            'company_name'  => 'nullable|string',
            'vat_number'    => 'nullable|string',
            'company_address' => 'nullable|string',
            'company_website' => 'nullable|string',
        ];

        return $rules;
    }
}
