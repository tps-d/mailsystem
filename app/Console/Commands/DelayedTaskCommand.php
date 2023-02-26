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

class DelayedTaskCommand extends Command
{
    /** @var string */
    protected $signature = 'sp:task:delayed';

    /** @var string */
    protected $description = 'Dispatch all campaigns from delayed task';

    /** @var CampaignDispatchService */
    protected $campaignService;

    public function handle(
        CampaignDispatchService $campaignService
    ): void {
        $this->campaignService = $campaignService;

        $tasks = $this->getQueuedTasks();
        $count = count($tasks);

        if (! $count) {
            return;
        }

        $this->info('Dispatching tasks count=' . $count);

        foreach ($tasks as $task) {
            $message = 'Dispatching task id=' . $task->id;

            $this->info($message);
            Log::info($message);
            
            $task->campaign->update([
                'status_id' => CampaignStatus::STATUS_QUEUED
            ]);

            $this->campaignService->handle($task->campaign);
        }

        $message = 'Finished dispatching campaigns';
        $this->info($message);
        Log::info($message);
    }

    /**
     * Get all queued tasks.
     */
    protected function getQueuedTasks(): EloquentCollection
    {
        return AutoTask::with('campaign')->where('type_id', 1)
            ->where('status_id', AutoTask::STATUS_RUNING)
            ->where('scheduled_at', '<=', now())
            ->get();
    }
}
