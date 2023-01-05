<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

class Variable extends BaseModel
{

    /** @var string */
    protected $table = 'sendportal_variable';

    /** @var array */
    protected $fillable = [
        'name',
        'description'
    ];

}
