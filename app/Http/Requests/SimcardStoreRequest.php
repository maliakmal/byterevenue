<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimcardStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number'       => 'required|string|max:255',
            'sms_capacity' => 'required|integer|max:255',
            'country_code' => 'required|string|max:255',
            'active'       => 'integer|max:255',
        ];
    }
}
