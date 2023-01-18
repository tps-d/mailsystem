<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Facades\MailSystem;

class AutomationsStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return $this->getRules();
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'name' => [
                'required',
                'max:255'
            ],
            'auto_label' => [
                'required',
                'max:255',
                Rule::unique('sendportal_automations')->where(function ($query) {
                    return $query->where('auto_label',$this->auto_label)->where('workspace_id',  MailSystem::currentWorkspaceId());
                }),
            ],
            'from_name' => [
                'required',
                'max:255',
            ],
            'from_email' => [
                'required',
                'max:255',
                'email',
            ],
            'email_service_id' => [
                'required',
                'integer',
                'exists:sendportal_email_services,id',
            ],
            'template_id' => [
                'nullable',
                'exists:sendportal_templates,id',
            ],
            'is_open_tracking' => [
                'boolean',
                'nullable'
            ],
            'is_click_tracking' => [
                'boolean',
                'nullable'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email_service_id.required' => __('Please select an email service.'),
        ];
    }
}
