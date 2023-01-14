<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CampaignDispatchRequest;
use App\Http\Resources\Campaign as CampaignResource;
use App\Services\QuotaService;
use App\Models\CampaignStatus;
use App\Repositories\CampaignRepository;

class CampaignDispatchController extends Controller
{

    protected $campaigns;

    /**
     * @var QuotaService
     */
    protected $quotaService;

    public function __construct(
        CampaignRepository $campaigns,
        QuotaService $quotaService
    ) {
        $this->campaigns = $campaigns;
        $this->quotaService = $quotaService;
    }

    /**
     * @throws \Exception
     */
    public function send(CampaignDispatchRequest $request, $campaignId)
    {
        $campaign = $request->getCampaign(['email_service', 'messages']);
        $workspaceId = MailSystem::currentWorkspaceId();

        if ($this->quotaService->exceedsQuota($campaign->email_service, $campaign->unsent_count)) {
            return response([
                'message' => __('The number of subscribers for this campaign exceeds your SES quota')
            ], 422);
        }

        $campaign = $this->campaigns->update($workspaceId, $campaignId, [
            'status_id' => CampaignStatus::STATUS_QUEUED,
        ]);

        return new CampaignResource($campaign);
    }
}
