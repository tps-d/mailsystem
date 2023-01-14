<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Facades\MailSystem;

/**
 * @property-read string $subscriber
 */
class SubscriberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('sendportal_subscribers', 'email')
                    ->ignore($this->subscriber, 'id')
                    ->where(static function (Builder $query) {
                        $query->where('workspace_id', MailSystem::currentWorkspaceId());
                    })
            ],
            'first_name' => [
                'max:255',
            ],
            'last_name' => [
                'max:255',
            ],
            'tags' => [
                'nullable',
                'array',
            ],
        ];
    }
}
