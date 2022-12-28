<?php

declare(strict_types=1);

namespace App\Repositories\Subscribers;

use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use App\Models\Subscriber;

interface SubscriberTenantRepositoryInterface 
{
    public function syncTags(Subscriber $subscriber, array $tags = []);

    public function countActive($workspaceId): int;

    public function getRecentSubscribers(int $workspaceId): Collection;

    public function getGrowthChartData(CarbonPeriod $period, int $workspaceId): array;
}
