<?php

namespace App\Repositories;

use App\Models\SocialService;
use App\Models\SocialServiceType;

class SocialServiceRepository extends BaseRepository
{

    /**
     * @var string
     */
    protected $modelName = SocialService::class;

    /**
     * @return mixed
     */
    public function getSocialServiceTypes()
    {
        return SocialServiceType::orderBy('name')->get();
    }

    /**
     * @param $socialServiceTypeId
     * @return mixed
     */
    public function findType($socialServiceTypeId)
    {
        return SocialServiceType::findOrFail($socialServiceTypeId);
    }

    /**
     * @param $socialServiceTypeId
     * @return array
     */
    public function findSettings($socialServiceTypeId)
    {
        if ($socialService = SocialService::where('type_id', $socialServiceTypeId)->first()) {
            return $socialService->settings;
        }

        return [];
    }
}
