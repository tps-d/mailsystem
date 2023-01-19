<?php

declare(strict_types=1);

namespace App\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Validation\ValidationException;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Repositories\CampaignRepository;

use App\Facades\MailSystem;

class CampaignRepeatController extends Controller
{

    private $campaignRepository;

    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @throws Exception
     */
    public function confirm(int $campaignId)
    {
        $campaign = $this->campaignRepository->find(MailSystem::currentWorkspaceId(), $campaignId, ['status']);

        return view('campaigns.repeat', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * @throws Exception
     */
    public function repeat(int $campaignId)
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepository->find(MailSystem::currentWorkspaceId(), $campaignId, ['status']);
        $originalStatus = $campaign->status;

        if (!$campaign->canBeRepeat()) {
            throw ValidationException::withMessages([
                'campaignStatus' => "{$campaign->status->name} campaigns cannot be repeated.",
            ])->redirectTo(route('campaigns.index'));
        }

        $this->campaignRepository->setRepeat($campaign);

        return redirect()->route('campaigns.index')->with([
            'success' => $this->getSuccessMessage($originalStatus, $campaign),
        ]);
    }

    private function getSuccessMessage(CampaignStatus $campaignStatus, Campaign $campaign): string
    {
        if ($campaign->is_repeat) {
            return __('The campaign set repeated successfully.');
        }

        if ($campaign->save_as_draft) {
            return __('The campaign was set repeated and any remaining draft messages were deleted.');
        }

        $messageCounts = $this->campaignRepository->getCounts(collect($campaign->id), $campaign->workspace_id)[$campaign->id];

        return __(
            "The campaign was set repeated whilst being processed (~:sent/:total dispatched).",
            [
                'sent' => $messageCounts->sent,
                'total' => $campaign->active_subscriber_count
            ]
        );
    }
}
