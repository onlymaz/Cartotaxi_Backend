<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
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

    public function rules()
    {
        return [
            'order_id'        => 'required|integer|exists:orders,id',
            // sign_image: must be an actual image file under 2 MB. The MIME
            // allowlist plus size cap is what prevents an attacker from
            // dropping a PHP/HTML/SVG payload into public storage.
            'sign_image'      => 'required|file|image|mimes:jpg,jpeg,png|max:2048',
            'another_reciver' => 'required|boolean',
            'reciver_name'    => 'required_if:another_reciver,1|string|max:191',
            'reciver_address' => 'required_if:another_reciver,1|string|max:500',
        ];
    }
}
