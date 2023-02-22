<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SocialServiceType;

class SocialServiceStoreRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'type_id' => ['required', 'integer'],

            'settings.key' => ['required'],
            'settings.token' => ['required_if:type_id,' . SocialServiceType::TELEGRAM]
        ];
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'settings.token.required_if' => __('The Social Service requires you to enter a token')
        ];
    }
}
