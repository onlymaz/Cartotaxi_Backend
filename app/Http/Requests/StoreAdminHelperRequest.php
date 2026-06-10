<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminHelperRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address'=>'required',
            'customer_id' => 'required',
            'total_helper' => 'required',
            'start' => 'required',
            'end' => 'required',
            'gateway' => 'required',
            'price'=>'required|numeric|min:0|gt:0',
            'time'=>'required'
        ];
    }
}
