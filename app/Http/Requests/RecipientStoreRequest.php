<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RecipientStoreRequest extends FormRequest
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
            'source' => 'nullable|string|min:1|max:100',
            'entry_type' => 'required|string',
            'name' => 'required|string|max:255',
            'name_column' => 'nullable|string|max:255',
            'email_column' => 'nullable|string|max:255',
            'phone_column' => 'nullable|string|max:255',
            'total_columns' => 'nullable|integer',
            'csv_file' => 'required_if:entry_type,file|file|mimes:csv,txt',
            'numbers' => 'nullable|string',
        ];
    }
}
