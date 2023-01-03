<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use App\Models\BaseModel;

class FailedJobs extends BaseModel
{
    protected $table = 'failed_jobs';
    public $timestamps = false;

    protected $dates = [
        'failed_at'
    ];
}
