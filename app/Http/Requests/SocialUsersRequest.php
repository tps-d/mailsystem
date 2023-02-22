<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Facades\MailSystem;

/**
 * @property-read string $subscriber
 */
class SocialUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'chat_id' => [
                'required',
                'max:255',
                Rule::unique('social_users', 'chat_id')
                    ->ignore($this->socialuser, 'id')
                    ->where(static function (Builder $query) {
                        $query->where('workspace_id', MailSystem::currentWorkspaceId());
                    })
            ],
            'username' => [
                'required',
                'max:255',
                Rule::unique('social_users', 'username')
                    ->ignore($this->socialuser, 'id')
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
