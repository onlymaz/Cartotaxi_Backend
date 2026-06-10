<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // A booking can only be created by an authenticated mobile user; the
        // route is protected by the authApi middleware which puts the user on
        // the request. Bookings are not authorized through a policy so there
        // is no per-row check required here.
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'picked_time'    => 'required|date',
            'package_id'     => 'required|integer|exists:packages,id',
            'transaction_id' => 'required|string|max:191',
            // payment_type is a closed enum. `card`/`cod` are the production
            // types; reject anything outside the list before it reaches the DB.
            'payment_type'   => 'required|in:cod,card',
            'poly_points'    => 'required|string',
            'with_helper'    => 'required|boolean',
            'total_helper'   => 'required_if:with_helper,1|integer|min:0|max:20',
            'helper_cost'    => 'required_if:with_helper,1|numeric|min:0',
            'hours'          => 'required_if:with_helper,1|integer|min:0|max:24',
            'location'       => 'required|json',
            // total_amount and total_meter are accepted from the client purely
            // for client-side echoing/diagnostics. They are NOT trusted: the
            // controller recomputes total_amount from the package + meters.
            'total_meter'    => 'required|integer|min:0',
            'total_second'   => 'required|integer|min:0',
            'total_amount'   => 'nullable|numeric|min:0',
            'description'    => 'nullable|string|max:1000',
            'response'       => 'nullable|string|max:5000',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'   => false,
            'messages' => implode(' ', array_column($validator->messages()->getMessages(), 0)),
            'errors'   => $validator->errors(),
        ], 422));
    }
}
