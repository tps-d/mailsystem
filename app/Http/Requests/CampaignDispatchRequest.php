<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Repositories\TagRepository;

use App\Facades\MailSystem;

class CampaignDispatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [

        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
