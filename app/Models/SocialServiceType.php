<?php

declare(strict_types=1);

namespace App\Models;

class SocialServiceType extends BaseModel
{
    protected $table = 'social_service_types';

    public const TELEGRAM = 1;

    /** @var array */
    protected static $types = [
        self::TELEGRAM => 'Telegram'
    ];

    /**
     * Resolve a type ID to a type name.
     */
    public static function resolve(int $typeId): ?string
    {
        return static::$types[$typeId] ?? null;
    }
}
