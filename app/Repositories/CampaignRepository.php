<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Models\CampaignTag;
use App\Repositories\BaseRepository;
use App\Traits\SecondsToHms;

class CampaignRepository extends BaseRepository
{

    use SecondsToHms;

    /** @var string */
    protected $modelName = Campaign::class;

    /**
     * {@inheritDoc}
     */
    public function completedCampaigns(int $workspaceId, array $relations = []): EloquentCollection
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('status_id', CampaignStatus::STATUS_SENT)
            ->with($relations)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getCounts(Collection $campaignIds, int $workspaceId): array
    {
        $counts = DB::table('sendportal_campaigns')
            ->leftJoin('sendportal_messages', function ($join) use ($campaignIds, $workspaceId) {
                $join->on('sendportal_messages.source_id', '=', 'sendportal_campaigns.id')
                    ->where('sendportal_messages.source_type', Campaign::class)
                    ->whereIn('sendportal_messages.source_id', $campaignIds)
                    ->where('sendportal_messages.workspace_id', $workspaceId);
            })
            ->select('sendportal_campaigns.id as campaign_id')
            ->selectRaw(sprintf('count(%ssendportal_messages.id) as total', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.opened_at IS NOT NULL then 1 end) as opened', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.clicked_at IS NOT NULL then 1 end) as clicked', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.sent_at IS NOT NULL then 1 end) as sent', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.bounced_at IS NOT NULL then 1 end) as bounced', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.sent_at IS NULL then 1 end) as pending', DB::getTablePrefix()))
            ->groupBy('sendportal_campaigns.id')
            ->orderBy('sendportal_campaigns.id')
            ->get();

        return $counts->flatten()->keyBy('campaign_id')->toArray();
    }

    public function setRepeat(Campaign $campaign): bool
    {
        $this->deleteDraftMessages($campaign);

        return $campaign->update([
            'status_id' => CampaignStatus::STATUS_LISTENING,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function cancelCampaign(Campaign $campaign): bool
    {
        $this->deleteDraftMessages($campaign);

        return $campaign->update([
            'status_id' => CampaignStatus::STATUS_CANCELLED,
        ]);
    }

    private function deleteDraftMessages(Campaign $campaign): void
    {
        //if (! $campaign->save_as_draft) {
        //    return;
        //}

        $campaign->messages()->whereNull('sent_at')->delete();
    }

    public function destroy($workspaceId, $id)
    {
        $instance = $this->find($workspaceId, $id);
        CampaignTag::where('campaign_id',$id)->delete();
        return $instance->delete();
    }

    /**
     * {@inheritDoc}
     */
    protected function applyFilters(Builder $instance, array $filters = []): void
    {
        $this->applySentFilter($instance, $filters);
    }

    /**
     * Filter by sent status.
     */
    protected function applySentFilter(Builder $instance, array $filters = []): void
    {
        if (Arr::get($filters, 'draft')) {
            $draftStatuses = [
                CampaignStatus::STATUS_DRAFT,
                CampaignStatus::STATUS_QUEUED,
                CampaignStatus::STATUS_SENDING,
            ];

            $instance->whereIn('status_id', $draftStatuses);
        } elseif (Arr::get($filters, 'sent')) {
            $sentStatuses = [
                CampaignStatus::STATUS_SENT,
                CampaignStatus::STATUS_CANCELLED,
            ];

            $instance->whereIn('status_id', $sentStatuses);
        } elseif (Arr::get($filters, 'repeat')) {
            $sentStatuses = [
                CampaignStatus::STATUS_LISTENING,
            ];

            $instance->whereIn('status_id', $sentStatuses);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAverageTimeToOpen(Campaign $campaign): string
    {
        $average = $campaign->opens()
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(SECOND, delivered_at, opened_at))) as average_time_to_open')
            ->value('average_time_to_open');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }

    /**
     * @inheritDoc
     */
    public function getAverageTimeToClick(Campaign $campaign): string
    {
        $average = $campaign->clicks()
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(SECOND, delivered_at, clicked_at))) as average_time_to_click')
            ->value('average_time_to_click');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }
}
