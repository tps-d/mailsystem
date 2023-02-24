<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignTestSocialRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recipient_chat_id' => [
                'required'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_chat_id.required' => __('A test chat id is required.'),
        ];
    }
}
