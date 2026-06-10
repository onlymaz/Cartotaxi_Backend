<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendFileToRiderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->role_id === 3;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id'   => 'required|integer|exists:orders,id',
            // Tighten: must be a real file under 5 MB and one of the allowed
            // types. `file` ensures we get an UploadedFile, not a string;
            // `mimes` enforces server-detected MIME, not just the extension.
            'image_name' => 'required|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ];
    }
}
