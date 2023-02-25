<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

use App\Repositories\TagRepository;

use App\Facades\MailSystem;

class CampaignStoreRequest extends FormRequest
{
    public function rules(): array
    {

        $tags = app(TagRepository::class)->pluck(
            MailSystem::currentWorkspaceId(),
            'id'
        );

        $rules = [
            'name' => [
                'required',
                'max:255'
            ],
            'template_id' => [
                'required',
                'exists:sendportal_templates,id',
            ],

            'is_send_mail' => [
                'boolean'
            ],
            'is_send_social' => [
                'boolean'
            ],

        ];

        if($this->input('is_send_mail') == 1){
            $rules = array_merge($rules,[

                'subject' => [
                    'required',
                    'max:255'
                ],
                'email_service_id' => [
                    'required',
                    'integer',
                    'exists:sendportal_email_services,id',
                ],
                'tags' => [
                    'required_unless:recipients,send_to_all',
                    'array',
                    Rule::in($tags),
                ],
/*
                'content' => [
                    Rule::requiredIf($this->template_id === null),
                ],
*/
                'is_open_tracking' => [
                    'boolean',
                    'nullable'
                ],
                'is_click_tracking' => [
                    'boolean',
                    'nullable'
                ],
            ]);


        }

        if($this->input('is_send_social') == 1){
            $rules = array_merge($rules,[
                'social_service_id' => [
                    'required',
                    'integer',
                    'exists:social_services,id',
                ],
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email_service_id.required_if' => __('Please select an email service.'),
            'tags.required_unless' => __('At least one tag must be selected'),
            'tags.in' => __('One or more of the tags is invalid.'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            
            if(!$this->is_send_mail && !$this->is_send_social){
                 $validator->errors()->add('send_type', 'Please select a send type ，send mail or send Social');
            }
           
        });
    }
}
