<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRegenerateRequest extends FormRequest
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
            'batch' => ['required', 'integer'],
            'url_shortener' => ['required', 'string'],
            'type' => ['string', 'in:campaign,fifo'],
            'campaign_ids' => ['required_if:type,campaign', 'array'],
            'campaign_ids.*' => ['required_if:type,campaign', 'integer'],
            'message_body' => ['required', 'string'],
        ];
    }
}
