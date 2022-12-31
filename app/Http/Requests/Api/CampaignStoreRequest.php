<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Http\Requests\CampaignStoreRequest as BaseCampaignStoreRequest;
use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Repositories\CampaignRepository;
use App\Repositories\TagRepository;

class CampaignStoreRequest extends BaseCampaignStoreRequest
{
    /**
     * @var CampaignRepository
     */
    protected $campaigns;

    public function __construct(CampaignRepository $campaigns)
    {
        parent::__construct();

        $this->campaigns = $campaigns;
        $this->workspaceId = 0;

        Validator::extendImplicit('valid_status', function ($attribute, $value, $parameters, $validator) {
            return $this->campaign
                ? $this->getCampaign()->status_id === CampaignStatus::STATUS_DRAFT
                : true;
        });
    }

    /**
     * @throws \Exception
     */
    public function getCampaign(): Campaign
    {
        return $this->campaign = $this->campaigns->find($this->workspaceId, $this->campaign);
    }

    public function rules(): array
    {
        $tags = app(TagRepository::class)->pluck(
            $this->workspaceId,
            'id'
        );

        $rules = [
            'send_to_all' => [
                'required',
                'boolean',
            ],
            'tags' => [
                'required_unless:send_to_all,1',
                'array',
                Rule::in($tags),
            ],
            'tags.*' => [
                'integer',
            ],
            'scheduled_at' => [
                'required',
                'date',
            ],
            'save_as_draft' => [
                'nullable',
                'boolean',
            ],
            'status_id' => 'valid_status',
        ];

        return array_merge($this->getRules(), $rules);
    }

    public function messages(): array
    {
        return [
            'valid_status' => __('A campaign cannot be updated if its status is not draft'),
            'tags.in' => 'One or more of the tags is invalid.',
        ];
    }
}
