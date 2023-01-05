<?php

declare(strict_types=1);

namespace App\Http\Controllers\Variable;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Http\Requests\VariableStoreRequest;
use App\Http\Requests\VariableUpdateRequest;
use App\Repositories\VariableRepository;

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
        $variables = $this->variableRepository->paginate(0, 'name');

        return view('variable.index', compact('variables'));
    }

    public function create(): View
    {
        return view('variable.create');
    }

    /**
     * @throws Exception
     */
    public function store(VariableStoreRequest $request): RedirectResponse
    {
        $this->variableRepository->store(0, $request->all());

        return redirect()->route('variable.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $variable = $this->variableRepository->find(0, $id);

        return view('variable.edit', compact('variable'));
    }

    /**
     * @throws Exception
     */
    public function update(int $id, VariableUpdateRequest $request): RedirectResponse
    {
        $this->variableRepository->update(0, $id, $request->all());

        return redirect()->route('variable.index');
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->variableRepository->destroy(0, $id);

        return redirect()->route('variable.index');
    }
}
