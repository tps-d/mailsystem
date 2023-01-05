<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TemplateStoreRequest;
use App\Http\Requests\Api\TemplateUpdateRequest;
use App\Http\Resources\Template as TemplateResource;
use App\Repositories\TemplateRepository;
use App\Services\Templates\TemplateService;
use App\Repositories\VariableRepository;

class TemplatesController extends Controller
{
    /** @var TemplateRepository */
    private $templates;

    /** @var TemplateService */
    private $service;

    /** @var VariableRepository */
    private $variable;

    public function __construct(TemplateRepository $templates, TemplateService $service ,VariableRepository $variable)
    {
        $this->templates = $templates;
        $this->service = $service;
        $this->variable = $variable;
    }

    /**
     * @throws Exception
     */
    public function index(): AnonymousResourceCollection
    {
        $workspaceId = 0;
        $templates = $this->templates->paginate($workspaceId, 'name');

        return TemplateResource::collection($templates);
    }


    /**
     * @throws Exception
     */
    public function show(int $id): TemplateResource
    {
        $workspaceId = 0;

        return new TemplateResource($this->templates->find($workspaceId, $id));
    }

    /**
     * @throws Exception
     */
    public function store(TemplateStoreRequest $request): TemplateResource
    {
        $workspaceId = 0;
        $template = $this->service->store($workspaceId, $request->validated());

        return new TemplateResource($template);
    }

    /**
     * @throws Exception
     */
    public function update(TemplateUpdateRequest $request, int $id): TemplateResource
    {
        $workspaceId = 0;
        $template = $this->service->update($workspaceId, $id, $request->validated());

        return new TemplateResource($template);
    }

    /**
     * @throws \Throwable
     */
    public function destroy(int $id): Response
    {
        $workspaceId = 0;
        $this->service->delete($workspaceId, $id);

        return response(null, 204);
    }
}
