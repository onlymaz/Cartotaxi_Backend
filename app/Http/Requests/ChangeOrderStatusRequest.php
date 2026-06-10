<?php

namespace App\Http\Requests;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow admin users (role_id == 1) or users with admin role
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->role_id == 1 || $user->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_status' => [
                'required',
                Rule::in([
                    'pending', 
                    'processing', 
                    'picking', 
                    'picked_up', 
                    'on_way', 
                    'delivered', 
                    'refused', 
                    'not_received', 
                    'accident', 
                    'cancel'
                ]),
            ],
            'signature' => ['nullable', 'sometimes', new Base64Image],
            'reciver_name' => ['nullable', 'string', 'max:255'],
            'reciver_address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
