<?php

declare(strict_types=1);

namespace App\Factories;

use InvalidArgumentException;

use App\Adapters\TelegramSocialAdapter;

use App\Models\SocialService;
use App\Models\SocialServiceType;

class SocialAdapterFactory
{
    /** @var array */
    public static $adapterMap = [
        SocialServiceType::TELEGRAM => TelegramSocialAdapter::class
    ];

    private $adapters = [];

    public function adapter(SocialService $socialService)
    {
        return $this->adapters[$socialService->id] ?? $this->cache($this->resolve($socialService), $socialService);
    }

    private function cache($adapter, SocialService $socialService)
    {
        return $this->adapters[$socialService->id] = $adapter;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function resolve(SocialService $socialService)
    {
        if (!$socialServiceType = SocialServiceType::resolve($socialService->type_id)) {
            throw new InvalidArgumentException("Unable to resolve social provider type from ID [$socialService->type_id].");
        }

        $adapterClass = self::$adapterMap[$socialService->type_id] ?? null;

        if (!$adapterClass) {
            throw new InvalidArgumentException("Social adapter type [{$socialServiceType}] is not supported.");
        }

        return new $adapterClass($socialService->settings);
    }
}
