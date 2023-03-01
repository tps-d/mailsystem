<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Facades\MailSystem;


class MessageDispatchRequest extends FormRequest
{

    public function rules()
    {
        return [
          //  'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => '邮箱格式不正确',
            'email.email' => '邮箱格式不正确'
        ];
    }

    protected function failedValidation( \Illuminate\Contracts\Validation\Validator $validator)
    {
        throw (new HttpResponseException(response()->json([
            'error' => $validator->errors()->first(),
        ], 200)));
    }
}
