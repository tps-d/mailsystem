<?php

declare(strict_types=1);

namespace App\Http\Controllers\Campaigns;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignDispatchRequest;
use App\Services\QuotaService;
use App\Models\CampaignStatus;
use App\Repositories\CampaignRepository;

use App\Facades\MailSystem;

class CampaignDispatchController extends Controller
{
    /** @var CampaignRepository */
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
     * Dispatch the campaign.
     *
     * @throws Exception
     */
    public function send(CampaignDispatchRequest $request, int $id): RedirectResponse
    {
        $campaign = $this->campaigns->find(MailSystem::currentWorkspaceId(), $id, ['email_service', 'messages']);

        if ($campaign->status_id !== CampaignStatus::STATUS_DRAFT) {
            return redirect()->route('campaigns.status', $id);
        }

        if (!$campaign->email_service_id) {
            return redirect()->route('campaigns.edit', $id)
                ->withErrors(__('Please select an Email Service'));
        }

        $campaign->update([
            'send_to_all' => $request->get('recipients') === 'send_to_all',
        ]);

        $campaign->tags()->sync($request->get('tags'));

        if ($this->quotaService->exceedsQuota($campaign->email_service, $campaign->unsent_count)) {
            return redirect()->route('campaigns.edit', $id)
                ->withErrors(__('The number of subscribers for this campaign exceeds your SES quota'));
        }

        $scheduledAt = $request->get('schedule') === 'scheduled' ? Carbon::parse($request->get('scheduled_at')) : now();

        $campaign->update([
            'scheduled_at' => $scheduledAt,
            'status_id' => CampaignStatus::STATUS_QUEUED,
            'save_as_draft' => $request->get('behaviour') === 'draft',
        ]);

        return redirect()->route('campaigns.status', $id);
    }
}
