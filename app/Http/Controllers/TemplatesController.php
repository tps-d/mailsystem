<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Http\Requests\TemplateStoreRequest;
use App\Http\Requests\TemplateUpdateRequest;
use App\Repositories\TemplateRepository;
use App\Repositories\VariableRepository;
use App\Services\Templates\TemplateService;
use App\Traits\NormalizeTags;
use Throwable;

class TemplatesController extends Controller
{
    use NormalizeTags;

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
    public function index(): View
    {
        $templates = $this->templates->paginate(0, 'name');

        return view('templates.index', compact('templates'));
    }

    public function create(): View
    {
        $variables = $this->variable->all(0);
        return view('templates.create', compact('variables'));
    }

    /**
     * @throws Exception
     */
    public function store(TemplateStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->service->store(0, $data);

        return redirect()
            ->route('templates.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $template = $this->templates->find(0, $id);

        $variables = $this->variable->all(0);

        return view('templates.edit', compact('template','variables'));
    }

    /**
     * @throws Exception
     */
    public function update(TemplateUpdateRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        $this->service->update(0, $id, $data);

        return redirect()
            ->route('templates.index');
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete(0, $id);

        return redirect()
            ->route('templates.index')
            ->with('success', __('Template successfully deleted.'));
    }
}
