<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Admin-only routes; the route group already enforces auth + admin role.
        return $this->user() !== null;
    }

    public function rules()
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'first_name'    => 'required|string|max:191',
            'last_name'     => 'required|string|max:191',
            'email'         => 'required|email|max:191|unique:users,email,' . $this->user,
            'phone_number'  => 'required|string|max:32|unique:users,phone_number,' . $this->user,
            // Password is required when creating; optional on update so the
            // admin can edit the profile without rotating the password (the
            // controller now also gates the actual rotation on filled()).
            'password'      => [$isUpdate ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'role_id'       => ['required', 'integer', 'exists:roles,id'],
            // Constrain the image type/size at the validation boundary. The
            // UploadImage trait enforces it again as defence-in-depth.
            'profile_image' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
