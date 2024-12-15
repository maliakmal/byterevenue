<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignUpdateRequest extends FormRequest
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
            'title' => 'string|max:255',
            'description' => 'required|string|min:3|max:50000',
            'recipients_list_id' => 'integer|exists:recipients_lists,id',
            'message_subject' => 'string|max:255',
            'message_body' => 'string',
            'message_target_url' => 'nullable|url',
        ];
    }
}
