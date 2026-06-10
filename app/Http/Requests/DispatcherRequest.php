<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DispatcherRequest extends FormRequest
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
        if (!empty(auth()->user()) && auth()->user()->role_id == 1){
            $rules['customer_id']   = 'required';
        }
        $rules['description']       = 'required';
        $rules['select_package']    = 'required';
        $rules['picked_date']       = 'required';
        $rules['picked_time']       = 'required';
        $rules['pick_location']     = 'required';
        $rules['end_location']      = 'required';
        $rules['select_package']    = 'required';
        $rules['total_amount']      = 'required';
        $rules['mid']               = 'required';
        $rules['start_address_district_id']= 'required';
        $rules['end_address_district_id']= 'required';
        $rules['district_ids']= 'required';
        foreach($this->request->get('mid') as $key => $val)
        {
            $rules['mid.'.$key] = 'required';
        }

        return $rules;
    }
}
