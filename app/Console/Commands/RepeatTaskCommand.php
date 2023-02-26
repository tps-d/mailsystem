<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;

use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Models\AutoTask;

use App\Services\Campaigns\CampaignDispatchService;

class RepeatTaskCommand extends Command
{
    /** @var string */
    protected $signature = 'sp:task:repeat {--task_id=}';

    /** @var string */
    protected $description = 'Dispatch all campaigns from repeat task';

    /** @var CampaignDispatchService */
    protected $campaignService;


    public function handle(
        CampaignDispatchService $campaignService
    ): void {

        $this->campaignService = $campaignService;

        $task_id = $this->option('task_id');

        $task = AutoTask::with('campaign')->where('id',$task_id)->first();
        if(!$task ){
            $message = 'Not found task with id '.$task->id;
            $this->info($message);
            Log::info($message); 
            return;
        }

        if(!$task->campaign){
            $message = 'Not found campaign in task '.$task->id;
            $this->info($message);
            Log::info($message); 
            return;
        }

        $copyCampaign = new Campaign();
        $copyCampaign->workspace_id = $task->campaign->workspace_id;
        $copyCampaign->name = $task->campaign->name.' - '.now();
        $copyCampaign->status_id = CampaignStatus::STATUS_QUEUED;
        $copyCampaign->template_id = $task->campaign->template_id;
        $copyCampaign->is_send_mail = $task->campaign->is_send_mail;
        $copyCampaign->is_send_social = $task->campaign->is_send_social;
        $copyCampaign->social_service_id = $task->campaign->social_service_id;
        $copyCampaign->email_service_id = $task->campaign->email_service_id;
        $copyCampaign->subject = $task->campaign->subject;
        $copyCampaign->content = $task->campaign->content;
        $copyCampaign->from_name = $task->campaign->from_name;
        $copyCampaign->from_email = $task->campaign->from_email;
        $copyCampaign->is_open_tracking = $task->campaign->is_open_tracking;
        $copyCampaign->is_click_tracking = $task->campaign->is_click_tracking;
        $copyCampaign->send_to_all = $task->campaign->send_to_all;
        $copyCampaign->scheduled_at = now();
        $copyCampaign->save();

        if($copyCampaign->is_send_mail && !$copyCampaign->send_to_all){

            $copyTags = [];
            $tags = $task->campaign->tags()->get();

            foreach($tags as $tag){
                $copyTags[] = $tag->id;
            }

            $copyCampaign->tags()->sync($tags);
        }

        $this->campaignService->handle($copyCampaign);

        $message = 'Finished dispatching copy campaigns from campaign_id '.$task->campaign->id;
        $this->info($message);
        Log::info($message);
    }
}
