<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ApiToken;
use App\Repositories\BaseRepository;

class ApiTokenRepository extends BaseRepository
{
    /** @var string */
    protected $modelName = ApiToken::class;
}
