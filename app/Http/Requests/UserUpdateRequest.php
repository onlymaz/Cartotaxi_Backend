<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'first_name'    =>  'required',
            'last_name'    =>  'required',
            'email'         => 'required|email|unique:users,email,'.$this->user,
            'phone_number'  =>   'required|unique:users,phone_number,'.$this->user,
            'role_id'   =>'required',
            "car_number"      =>  "required_if:role_id,==,2",

        ];
        return $rules;
    }
}
