<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Cron\CronExpression;

use InvalidArgumentException;
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
            'scheduled_at'         => 'required_if:type_id,1|date',
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            
            if($this->type_id == 2 && $this->expression){
                try{
                    CronExpression::factory($this->expression);
                    
                }catch(InvalidArgumentException $e){
                    $validator->errors()->add('expression', 'Cron Expression  is not a valid position');
                }
            }
           
        });
    }

}
