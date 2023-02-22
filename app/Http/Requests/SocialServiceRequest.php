<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SocialServiceType;
use Illuminate\Validation\Validator;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SocialServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            return $this->checkTelegramToken($validator);
        });
    }

    public function checkTelegramToken(Validator $validator){
        $token = $this->settings['token'];

        try{
            $telegram = new Api($token);
            $response = $telegram->getMe();
        }catch(TelegramSDKException $e){
            $validator->errors()->add('settings.token', 'Exception with api token: '.$e->getMessage());
            return false;
        }

        $this->merge([
            'bot_id' => $response->getId(),
            'bot_username' => $response->getUsername()
        ]);

        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => ['required']
        ];

        if (!$this->route('id')) {
            $rules['type_id'] = ['required', 'integer'];
        }

        return array_merge($rules, self::resolveValidationRules($this->input('type_id')));
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        switch ((int) $this->input('type_id')) {
            case SocialServiceType::TELEGRAM:
                return [
                    'settings.token.required' => __('The Telegram Service requires you to enter a token')
                ];

            default:
                return [];
        }
    }

    public static function resolveValidationRules($typeId): array
    {
        switch ($typeId) {
            case SocialServiceType::TELEGRAM:
                return [
                    'settings.token' => 'required'
                ];

            default:
                return [];
        }
    }
}
