<?php

namespace App\Services\Campaigns;

use Illuminate\Pipeline\Pipeline;
use App\Models\Campaign;
use App\Pipelines\Campaigns\CompleteCampaign;
use App\Pipelines\Campaigns\CreateMessages;
use App\Pipelines\Campaigns\StartCampaign;

class CampaignDispatchService
{
    /**
     * Dispatch the campaign
     *
     * @param Campaign $campaign
     * @return void
     */
    public function handle(Campaign $campaign)
    {
        // check if the campaign still exists
        if (! $campaign = $this->findCampaign($campaign->id)) {
            return;
        }

        if (! $campaign->queued) {
            \Log::error('Campaign does not have a queued status campaign_id=' . $campaign->id . ' status_id=' . $campaign->status_id);

            return;
        }

        $pipes = [
            StartCampaign::class,
            CreateMessages::class,
            CompleteCampaign::class,
        ];

        try {
            app(Pipeline::class)
                ->send($campaign)
                ->through($pipes)
                ->then(function ($campaign) {
                    return $campaign;
                });
        } catch (\Exception $exception) {
            \Log::error('Error dispatching campaign id=' . $campaign->id . ' exception=' . $exception->getMessage() . ' trace=' . $exception->getTraceAsString());
        }
    }

    /**
     * Find a single campaign schedule
     *
     * @param int $id
     * @return Campaign|null
     */
    protected function findCampaign(int $id): ?Campaign
    {
        return Campaign::with('tags')->find($id);
    }
}
