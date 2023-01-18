<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Facades\MailSystem;

class TemplateStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sendportal_templates')
                    ->where('workspace_id', MailSystem::currentWorkspaceId()),
            ],
            'content' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => __('The template name must be unique.'),
        ];
    }
}
