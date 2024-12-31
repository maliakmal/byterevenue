<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignStoreRequest extends FormRequest
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
            'is_template' => 'required|boolean',
            'title' => 'required_if:is_template,false|nullable|string|max:255',
            'campaign_id' => 'nullable|numeric|exists:campaigns,id',
            'description' => 'nullable|string',
            'recipients_list_id' => 'required_if:is_template,false|nullable|integer|exists:recipients_lists,id',
            'message_subject' => 'required_if:is_template,false|nullable|string|max:255',
            'message_body' => 'required_if:is_template,false|nullable|string',
            'message_target_url' => 'required_if:is_template,false|nullable|string|max:255',
        ];
    }
}
