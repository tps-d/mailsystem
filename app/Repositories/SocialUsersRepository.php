<?php

namespace App\Repositories;

use Exception;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Models\SocialUser;
use App\Repositories\BaseRepository;

class SocialUsersRepository extends BaseRepository
{

    /** @var string */
    protected $modelName = SocialUser::class;

    /**
     * {@inheritDoc}
     */
    public function store($workspaceId, array $data)
    {
        $this->checkTenantData($data);

        /** @var Subscriber $instance */
        $instance = $this->getNewInstance();

        $subscriber = $this->executeSave($workspaceId, $instance, Arr::except($data, []));

        return $subscriber;
    }


    /**
     * {@inheritDoc}
     */
    public function update($workspaceId, $id, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->find($workspaceId, $id);

        $subscriber = $this->executeSave($workspaceId, $instance, Arr::except($data, ['id']));

        return $subscriber;
    }

    /**
     * Return the count of active subscribers
     *
     * @param int $workspaceId
     *
     * @return mixed
     * @throws Exception
     */
    public function countActive($workspaceId): int
    {
        return $this->getQueryBuilder($workspaceId)
            ->whereNull('unsubscribed_at')
            ->count();
    }

    public function getRecentSubscribers(int $workspaceId): Collection
    {
        return $this->getQueryBuilder($workspaceId)
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();
    }

    /**
     * @inheritDoc
     */
    protected function applyFilters(Builder $instance, array $filters = []): void
    {
        $this->applyNameFilter($instance, $filters);
        $this->applyStatusFilter($instance, $filters);
    }

    /**
     * Filter by name or email.
     */
    protected function applyNameFilter(Builder $instance, array $filters): void
    {
        if ($name = Arr::get($filters, 'name')) {
            $filterString = '%' . $name . '%';

            $instance->where(static function (Builder $instance) use ($filterString) {
                $instance->where('social_users.username', 'like', $filterString);
            });
        }
    }

    /**
     * Filter by subscription status.
     */
    protected function applyStatusFilter(Builder $instance, array $filters): void
    {
        $status = Arr::get($filters, 'status');

        if ($status === 'subscribed') {
            $instance->whereNull('unsubscribed_at');
        } elseif ($status === 'unsubscribed') {
            $instance->whereNotNull('unsubscribed_at');
        }
    }

    /**
     * @inheritDoc
     */
    public function getGrowthChartData(CarbonPeriod $period, int $workspaceId): array
    {
        $startingValue = DB::table('social_users')
            ->where('workspace_id', $workspaceId)
            ->where(function (\Illuminate\Database\Query\Builder $q) use ($period) {
                $q->where('unsubscribed_at', '>=', $period->getStartDate())
                    ->orWhereNull('unsubscribed_at');
            })
            ->where('created_at', '<', $period->getStartDate())
            ->count();

        $runningTotal = DB::table('social_users')
            ->selectRaw("date_format(created_at, '%d-%m-%Y') AS date, count(*) as total")
            ->where('workspace_id', $workspaceId)
            ->where('created_at', '>=', $period->getStartDate())
            ->where('created_at', '<=', $period->getEndDate())
            ->groupBy('date')
            ->get();

        $unsubscribers = DB::table('social_users')
            ->selectRaw("date_format(unsubscribed_at, '%d-%m-%Y') AS date, count(*) as total")
            ->where('workspace_id', $workspaceId)
            ->where('unsubscribed_at', '>=', $period->getStartDate())
            ->where('unsubscribed_at', '<=', $period->getEndDate())
            ->groupBy('date')
            ->get();

        return [
            'startingValue' => $startingValue,
            'runningTotal' => $runningTotal->flatten()->keyBy('date'),
            'unsubscribers' => $unsubscribers->flatten()->keyBy('date'),
        ];
    }
}
