<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\AutoTrigger;
use App\Repositories\BaseRepository;
use App\Traits\SecondsToHms;

class AutotriggerRepository extends BaseRepository
{


    /** @var string */
    protected $modelName = AutoTrigger::class;

    protected function applyFilters(Builder $instance, array $filters = []): void
    {

        if ($from_type = Arr::get($filters, 'from_type')) {
            $instance->where('from_type', $from_type);
        }
    }

    public function cancelAutotrigger(AutoTrigger $autotrigger): bool
    {
        return $autotrigger->update([
            'status_id' => AutoTrigger::STATUS_CANCELLED,
        ]);
    }

    public function activeAutotrigger(AutoTrigger $autotrigger): bool
    {
        return $autotrigger->update([
            'status_id' => AutoTrigger::STATUS_ACTIVE,
        ]);
    }
    
}
