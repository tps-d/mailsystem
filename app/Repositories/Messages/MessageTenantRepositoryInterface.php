<?php

declare(strict_types=1);

namespace App\Repositories\Messages;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MessageTenantRepositoryInterface
{
    public function paginateWithSource(int $workspaceId, string $orderBy = 'name', array $relations = [], int $paginate = 25, array $parameters = []): LengthAwarePaginator;

    public function recipients(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function opens(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function clicks(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function bounces(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function unsubscribes(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function getFirstOpenedAt(int $workspaceId, string $sourceType, int $sourceId);

    /**
     * Count the number of unique opens per period for a campaign or automation schedule.
     */
    public function countUniqueOpensPerPeriod(int $workspaceId, string $sourceType, int $sourceId, int $intervalInSeconds): Collection;
}
