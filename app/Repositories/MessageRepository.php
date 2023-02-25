<?php

namespace App\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Facades\Helper;
use App\Models\Campaign;
use App\Models\Message;
use App\Repositories\BaseRepository;

class MessageRepository extends BaseRepository
{

    /** @var string */
    protected $modelName = Message::class;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function paginateWithSource(int $workspaceId, string $orderBy = 'name', array $relations = [], int $paginate = 25, array $parameters = []): LengthAwarePaginator
    {
        $this->parseOrder($orderBy);

        $instance = $this->getQueryBuilder($workspaceId)
            ->with([
                'source' => static function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        AutomationSchedule::class => ['automation_step.automation:id,name'],
                    ]);
                }
            ]);

        $instance->where('source_type', '=', Campaign::class);

        $this->applyFilters($instance, $parameters);

        return $instance
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->paginate($paginate);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function recipients(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('sent_at')
            ->orderBy('recipient_email')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function opens(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->orderBy('opened_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function clicks(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('clicked_at')
            ->orderBy('clicked_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function bounces(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->with(['failures'])
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('bounced_at')
            ->orderBy('bounced_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function unsubscribes(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('unsubscribed_at')
            ->orderBy('unsubscribed_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     */
    public function getFirstOpenedAt(int $workspaceId, string $sourceType, int $sourceId)
    {
        return DB::table('sendportal_messages')
            ->select(DB::raw('MIN(opened_at) as first'))
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->value('first');
    }

    /**
     * @inheritDoc
     */
    protected function applyFilters(Builder $instance, array $filters = []): void
    {
        $this->applySentFilter($instance, $filters);
        $this->applySearchFilter($instance, $filters);
        $this->applyStatusFilter($instance, $filters);
    }

    /**
     * Filter by sent status.
     */
    protected function applySentFilter(Builder $instance, array $filters = []): void
    {
        if ($sentAt = Arr::get($filters, 'draft')) {
            $instance->whereNotNull('queued_at')
                ->whereNull('sent_at');
        } elseif ($sentAt = Arr::get($filters, 'sent')) {
            $instance->whereNotNull('sent_at');
        }
    }

    /**
     * Apply a search filter over recipient email or subject.
     */
    protected function applySearchFilter(Builder $instance, array $filters = []): void
    {
        if ($search = Arr::get($filters, 'search')) {
            $searchString = '%' . $search . '%';

            $instance->where(static function (Builder $instance) use ($searchString) {
                $instance->where('sendportal_messages.recipient_email', 'like', $searchString)
                    ->orWhere('sendportal_messages.subject', 'like', $searchString);
            });
        }

        if ($source_id = Arr::get($filters, 'source_id')) {
            $instance->where('source_id',$source_id);
        }
    }

    /**
     * Filter by status.
     *
     * Note that when we use this filter, we only expect messages that are *at* that status. For example, if
     * a message has been "clicked", then it will not also appear in the "sent" or "delivered" statuses.
     */
    protected function applyStatusFilter(Builder $instance, array $filters = [])
    {
        $status = Arr::get($filters, 'status', 'all');

        if ($status === 'bounced') {
            $instance->whereNotNull('bounced_at');
        } elseif ($status === 'unsubscribed') {
            $instance->whereNotNull('unsubscribed_at');
        } elseif ($status === 'clicked') {
            $instance->whereNotNull('clicked_at');
        } elseif ($status === 'opened') {
            $instance->whereNotNull('opened_at')
                ->whereNull('clicked_at');
        } elseif ($status === 'delivered') {
            $instance->whereNotNull('delivered_at')
                ->whereNull('opened_at');
        } elseif ($status === 'sent') {
            $instance->whereNull('delivered_at');
        }
    }

    /**
     * @inheritDoc
     */
    public function countUniqueOpensPerPeriod(int $workspaceId, string $sourceType, int $sourceId, int $intervalInSeconds): Collection
    {
        return DB::table('sendportal_messages')
            ->selectRaw('COUNT(*) as open_count, MIN(opened_at) as opened_at, FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(opened_at) DIV ' . $intervalInSeconds . ') * ' . $intervalInSeconds . ') as period_start')
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->groupByRaw('UNIX_TIMESTAMP(opened_at) DIV ' . $intervalInSeconds)
            ->orderBy('opened_at')
            ->get();
    }
}
