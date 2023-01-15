<?php

declare(strict_types=1);

namespace App\Http\Controllers\Variable;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Http\Requests\VariableStoreRequest;
use App\Http\Requests\VariableUpdateRequest;

use App\Models\Variable;
use App\Repositories\VariableRepository;
use App\Facades\MailSystem;

class VariableController extends Controller
{
    /** @var VariableRepository */
    private $variableRepository;

    public function __construct(VariableRepository $variableRepository)
    {
        $this->variableRepository = $variableRepository;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $variables = $this->variableRepository->paginate(MailSystem::currentWorkspaceId(), 'name');

        return view('variable.index', compact('variables'));
    }

    public function create(): View
    {   
        $value_types = Variable::$value_types_map;
        return view('variable.create', compact('value_types'));
    }

    /**
     * @throws Exception
     */
    public function store(VariableStoreRequest $request): RedirectResponse
    {
        $this->variableRepository->store(MailSystem::currentWorkspaceId(), $request->all());

        $this->variableRepository->rebuildCache(MailSystem::currentWorkspaceId());

        return redirect()->route('variable.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $variable = $this->variableRepository->find(MailSystem::currentWorkspaceId(), $id);
        $value_types = Variable::$value_types_map;
        return view('variable.edit', compact('variable','value_types'));
    }

    /**
     * @throws Exception
     */
    public function update(int $id, VariableUpdateRequest $request): RedirectResponse
    {
        $this->variableRepository->update(MailSystem::currentWorkspaceId(), $id, $request->all());

        $this->variableRepository->rebuildCache(MailSystem::currentWorkspaceId());

        return redirect()->route('variable.index');
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->variableRepository->destroy(MailSystem::currentWorkspaceId(), $id);

        $this->variableRepository->rebuildCache();

        return redirect()->route('variable.index');
    }
}
