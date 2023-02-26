<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

use App\Console\Commands\RepeatTaskCommand;
use App\Models\AutoTask;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register any services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->resolving(Schedule::class, function ($schedule) {
            $this->schedule($schedule);
        });
    }

    /**
     * Prepare schedule from tasks.
     *
     * @param  Schedule  $schedule
     */
    public function schedule(Schedule $schedule)
    {
        $tasks = AutoTask::where('type_id',2)->where('status_id',AutoTask::STATUS_RUNING)->get();

        $tasks->each(function ($task) use ($schedule) {
            $schedule->command(RepeatTaskCommand::class, ['--task_id='.$task->id])->cron($task->expression)->withoutOverlapping();
        });
    }
}