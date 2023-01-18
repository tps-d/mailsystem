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
        /** @var TagRepository $tags */
        $tags = app(TagRepository::class)->pluck(
            MailSystem::currentWorkspaceId(),
            'id'
        );

        return [
            'tags' => [
                'required_unless:recipients,send_to_all',
                'array',
                Rule::in($tags),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tags.required_unless' => __('At least one tag must be selected'),
            'tags.in' => __('One or more of the tags is invalid.'),
        ];
    }
}
