<?php

declare(strict_types=1);

namespace App\Services\Tags;

use Exception;
use Illuminate\Support\Collection;
use App\Models\Tag;
use App\Repositories\TagRepository;

class ApiTagService
{
    /** @var TagRepository */
    private $tags;

    public function __construct(TagRepository $tags)
    {
        $this->tags = $tags;
    }

    /**
     * Store a new tag, optionally including attached subscribers.
     *
     * @throws Exception
     */
    public function store(int $workspaceId, Collection $data): Tag
    {
        $tag = $this->tags->store($workspaceId, $data->except('subscribers')->toArray());

        if (!empty($data['subscribers'])) {
            $tag->subscribers()->attach($data['subscribers']);
        }

        return $tag;
    }
}
