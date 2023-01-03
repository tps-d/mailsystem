<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    protected $table = 'jobs';
    public $timestamps = false;

    protected $dates = [
        'reserved_at',
        'available_at',
        'created_at'
    ];

    protected $casts = [
        'attempts' => 'int',
        'reserved_at' => 'datetime',
        'available_at' => 'datetime',
        'created_at' => 'datetime'
    ];
}