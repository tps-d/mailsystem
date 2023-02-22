<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoTrigger extends BaseModel
{

    /** @var string */
    protected $table = 'auto_trigger';

    public const STATUS_ACTIVE = 1;
    public const STATUS_CANCELLED = 2;
    
    public $status_map = [
        AutoTrigger::STATUS_ACTIVE => 'Active',
        AutoTrigger::STATUS_CANCELLED => 'Cancelled'
    ];

    public const CONDITION_ALL = 'all';
    public const CONDITION_INCLUDE = 'include';
    
    public $condition_map = [
        AutoTrigger::CONDITION_ALL => '所有内容',
        AutoTrigger::CONDITION_INCLUDE => '包含'
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
        'workspace_id',
        'name',
        'from_type',
        'from_id',
        'condition',
        'match_content',
        'template_id',
        'status_id'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
 
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function getStatusTitleAttribute()
    {
        return $this->status_map[$this->status_id];
    }

    public function getConditionTitleAttribute()
    {
        $title = $this->condition_map[$this->condition];
        if($this->condition == AutoTrigger::CONDITION_INCLUDE){
            $title .= ': "'.$this->match_content.'"';
        }
        return $title;
    }

    public function canBeCancel(): bool
    {
        if (
            $this->status_id === AutoTrigger::STATUS_ACTIVE
        ) {
            return true;
        }

        return false;
    }

    public function canBeActive(): bool
    {
        if (
            $this->status_id === AutoTrigger::STATUS_CANCELLED
        ) {
            return true;
        }

        return false;
    }
}
