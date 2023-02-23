<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Facades\MailSystem;

class AutomationsStoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type_id'              => 'required',
            'campaign_id'          => 'required',
            'scheduled_at'         => 'required|date',
            'expression'           => 'required_if:type_id,2',
            
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
            'campaign_id.required'                                => 'Please select a campaign',
            'expression.required_if'                          => 'Cron Expression is required if task type is expression',
        ];
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        if ($this->input('type_id') == 2) {
            $this->merge(['expression' => null]);
        }

        return $this->all();
    }
}
