<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BroadcastBatchStoreRequest extends FormRequest
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
            'campaign_id' => ['required', 'integer'],
            'message_subject' => ['required', 'string'],
            'message_body' => ['required', 'string'],
            'message_target_url' => ['required', 'string'],
            'recipients_list_id' => ['required', 'integer'],
        ];
    }
}
