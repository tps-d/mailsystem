<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class MessageDispatchRequest extends FormRequest
{

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'template_label' => ['required']
        ];
    }
}
