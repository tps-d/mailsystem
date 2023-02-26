<?php

namespace App\Pipelines\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Models\AutoTask;

class CompleteCampaign
{
    /**
     * Mark the campaign as complete in the database
     *
     * @param Campaign $schedule
     * @return Campaign
     */
    public function handle(Campaign $schedule, $next)
    {
        $this->markCampaignAsComplete($schedule);

        return $next($schedule);
    }

    /**
     * Execute the database query
     *
     * @param Campaign $campaign
     * @return void
     */
    protected function markCampaignAsComplete(Campaign $campaign): void
    {
        $campaign->status_id = CampaignStatus::STATUS_SENT;
        $campaign->save();

        $autoTask = AutoTask::where('campaign_id',$campaign->id)->first();
        if($autoTask && $autoTask->type_id == 1){
            $autoTask->last_ran_at = now();
            $autoTask->status_id = AutoTask::STATUS_FINISHED;
            $autoTask->save();
        }
    }
}
