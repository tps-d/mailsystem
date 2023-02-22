<?php

declare(strict_types=1);

namespace App\Http\Controllers\Automations;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutomationsStoreRequest;
use App\Repositories\AutomationsRepository;
use App\Facades\MailSystem;

use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Models\AutoTask;

class AutomationsController extends Controller
{
    /** @var AutomationsRepository */
    protected $automations;


    public function __construct(
        AutomationsRepository $automations
    ) {
        $this->automations = $automations;
    }

    /**
     * @throws Exception
     */
    public function index(): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();

        $automations = $this->automations->paginate($workspaceId,'created_atDesc',['campaign']);

        return view('automations.index', [
            'automations' => $automations,
            //'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspaceId),
        ]);
    }

    /**
     * @throws Exception
     */
    public function create(): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaigns = Campaign::where('workspace_id',$workspaceId)->where('status_id', CampaignStatus::STATUS_DRAFT)->pluck('name', 'id')->all();

        return view('automations.create', compact('campaigns'));
    }

    /**
     * @throws Exception
     */
    public function store(AutomationsStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->automations->store($workspaceId, [
            'campaign_id' => $request->campaign_id,
            'type_id' => $request->type_id,
            'expression' => $request->expression
        ]);

        return redirect()->route('automations.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $task = $this->automations->find($workspaceId, $id);
        $campaigns = Campaign::where('workspace_id',$workspaceId)->where('status_id', CampaignStatus::STATUS_DRAFT)->pluck('name', 'id')->all();
        return view('automations.edit', compact('task', 'campaigns'));
    }

    /**
     * @throws Exception
     */
    public function update(int $taskId, AutomationsStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->automations->update(
            $workspaceId,
            $taskId,
             [
                'campaign_id' => $request->campaign_id,
                'type_id' => $request->type_id,
                'expression' => $request->expression
            ]
        );

        return redirect()->route('automations.index', $campaign->id);
    }


    public function stop(int $id)
    {
        $automations = $this->automations->find(MailSystem::currentWorkspaceId(), $id);


        if ($automations->status_id !== AutoTask::STATUS_RUNING) {
            throw ValidationException::withMessages([
                'campaignStatus' => "{$automations->status_title} task cannot be cancelled.",
            ])->redirectTo(route('automations.index'));
        }

        $this->automations->stopAutomation($automations);

        return redirect()->route('automations.index')->with([
            'success' => 'The trigger was cancelled successfully.',
        ]);
    }

    public function start(int $id)
    {
        $automations = $this->automations->find(MailSystem::currentWorkspaceId(), $id);

        if ($automations->status_id != AutoTask::STATUS_STOP) {
            throw ValidationException::withMessages([
                'campaignStatus' => "{$automations->status_title} task cannot be start.",
            ])->redirectTo(route('automations.index'));
        }

        $this->automations->startAutomation($automations);

        return redirect()->route('automations.index')->with([
            'success' => 'The task was actived successfully.',
        ]);
    }

    public function confirm(int $id)
    {
        $automations = $this->automations->find(MailSystem::currentWorkspaceId(), $id);

        if ($automations->status == AutoTask::STATUS_RUNING) {
            return redirect()->route('automations.index')
                ->withErrors(__('Unable to delete a task that is not in cancel status'));
        }

        return view('automations.delete', compact('automations'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $automations = $this->automations->find(MailSystem::currentWorkspaceId(), $request->get('id'));

        if ($automations->status == AutoTask::STATUS_RUNING) {
            return redirect()->route('automations.index')
                ->withErrors(__('Unable to delete a task that is not in cancel status'));
        }

        $this->automations->destroy(MailSystem::currentWorkspaceId(), $request->get('id'));

        return redirect()->route('automations.index',['type'=>$automations->from_type])
            ->with('success', __('The task has been successfully deleted'));
    }

}
