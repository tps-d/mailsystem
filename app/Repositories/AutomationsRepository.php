<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\AutoTask;
use App\Repositories\BaseRepository;
use App\Traits\SecondsToHms;

class AutomationsRepository extends BaseRepository
{

    use SecondsToHms;

    /** @var string */
    protected $modelName = AutoTask::class;


    public function stopAutomation(AutoTask $task): bool
    {
        return $task->update([
            'status_id' => AutoTask::STATUS_STOP,
        ]);
    }

    public function startAutomation(AutoTask $task): bool
    {
        return $task->update([
            'status_id' => AutoTask::STATUS_RUNING,
        ]);
    }


    /**
     * Execute a given task.
     *
     * @param $id
     * @return int|Task
     */
    public function execute($id)
    {
        $task = $this->find($id);
        $start = microtime(true);
        try {
            Artisan::call($task->command, $task->compileParameters());
            $output = Artisan::output();
        } catch (\Exception $e) {
            $output = $e->getMessage();
        }

        Executed::dispatch($task, $start, $output);

        return $task;
    }

}
