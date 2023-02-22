<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Facades\MailSystem;

class AutotriggerStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                    => 'required',
            'from_type'                    => 'required',
            'from_id'                 => 'required',
            'template_id'                 => 'required',
            'condition'                 => 'required',
            'match_content'                => 'required_if:condition,include',
            'template_id'             => 'required'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'                            => 'Task name is required',
            'from_type.required'                                => 'Please select a source from',
            'template_id.required'                                => 'Please select a reply template',
            'match_content.required_if'                          => 'Match content is required if condition is include',
        ];
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        if ($this->input('condition') == 'all') {
            $this->merge(['match_content' => null]);
        }

        return $this->all();
    }
}
