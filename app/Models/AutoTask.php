<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoTask extends BaseModel
{

    /** @var string */
    protected $table = 'auto_tasks';

    public const STATUS_RUNING = 1;
    public const STATUS_STOP = 2;
    public const STATUS_FINISHED = 3;
    public const STATUS_ERROR = 4;
    
    public $status_map = [
        AutoTask::STATUS_RUNING => 'Running',
        AutoTask::STATUS_STOP => 'Stopped',
        AutoTask::STATUS_FINISHED => 'Finished',
        AutoTask::STATUS_ERROR => 'Error'
    ];

    /** @var array */
    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type_id',
        'campaign_id',
        'expression',
        'status_id'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
 
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }


    /**
     * Upcoming Accessor.
     *
     * @return string
     */
    public function getUpcomingAttribute()
    {
        return CronExpression::factory($this->expression)->getNextRunDate()->format('Y-m-d H:i:s');
    }

    public function getStatusTitleAttribute()
    {
        return $this->status_map[$this->status_id];
    }

    public function canBeStop(): bool
    {
        if (
            $this->status_id === AutoTask::STATUS_RUNING
        ) {
            return true;
        }

        return false;
    }

    public function canBeStart(): bool
    {
        if (
            $this->status_id === AutoTask::STATUS_STOP
        ) {
            return true;
        }

        return false;
    }
}
