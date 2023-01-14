<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CampaignStoreRequest;
use App\Http\Resources\Campaign as CampaignResource;
use App\Repositories\CampaignRepository;

class CampaignsController extends Controller
{

    private $campaigns;

    public function __construct(CampaignRepository $campaigns)
    {
        $this->campaigns = $campaigns;
    }

    /**
     * @throws Exception
     */
    public function index(): AnonymousResourceCollection
    {
        $workspaceId = MailSystem::currentWorkspaceId();

        return CampaignResource::collection($this->campaigns->paginate($workspaceId, 'id', ['tags']));
    }

    /**
     * @throws Exception
     */
    public function store(CampaignStoreRequest $request): CampaignResource
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $data = Arr::except($request->validated(), ['tags']);

        $data['save_as_draft'] = $request->get('save_as_draft') ?? 0;

        $campaign = $this->campaigns->store($workspaceId, $data);

        $campaign->tags()->sync($request->get('tags'));

        return new CampaignResource($campaign);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): CampaignResource
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->campaigns->find($workspaceId, $id);

        return new CampaignResource($campaign);
    }

    /**
     * @throws Exception
     */
    public function update(CampaignStoreRequest $request, int $id): CampaignResource
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $data = Arr::except($request->validated(), ['tags']);

        $data['save_as_draft'] = $request->get('save_as_draft') ?? 0;

        $campaign = $this->campaigns->update($workspaceId, $id, $data);

        $campaign->tags()->sync($request->get('tags'));

        return new CampaignResource($campaign);
    }
}
