<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64Image implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        $decoded_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value), true);

        // Check if the decoded data is valid
        if ($decoded_data === false) {
            return false;
        }

        // Check if the decoded data is a valid image
        $image_info = getimagesizefromstring($decoded_data);
        if ($image_info === false) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid base64 encoded image.';
    }
}
