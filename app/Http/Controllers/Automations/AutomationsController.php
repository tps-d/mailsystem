<?php

declare(strict_types=1);

namespace App\Http\Controllers\Automations;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutomationsStoreRequest;
use App\Repositories\AutomationsRepository;
use App\Repositories\CampaignRepository;

use App\Facades\MailSystem;

use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Models\AutoTask;

class AutomationsController extends Controller
{
    /** @var AutomationsRepository */
    protected $automations;

    /** @var CampaignRepository */
    protected $campaigns;

    public function __construct(
        AutomationsRepository $automations,
        CampaignRepository $campaigns
    ) {
        $this->automations = $automations;
        $this->campaigns = $campaigns;
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
    public function create(Request $request): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaigns = Campaign::where('workspace_id',$workspaceId)->where('status_id', CampaignStatus::STATUS_DRAFT)->pluck('name', 'id')->all();

        $task = new AutoTask();
        if($campaign_id = $request->get('campaign_id')){
            $task->campaign_id = $campaign_id;
        }
        
        return view('automations.create', compact('task','campaigns'));
    }

    /**
     * @throws Exception
     */
    public function store(AutomationsStoreRequest $request): RedirectResponse
    {

        $workspaceId = MailSystem::currentWorkspaceId();

        $campaign = $this->campaigns->find(MailSystem::currentWorkspaceId(), $request->campaign_id, ['status']);

        $automation = $this->automations->store($workspaceId, [
            'campaign_id' => $request->campaign_id,
            'type_id' => $request->type_id,
            'scheduled_at' => $request->scheduled_at,
            'expression' => $request->expression
        ]);

        if($request->type_id == 1){

            $campaign->update([
                'status_id' => CampaignStatus::STATUS_DELAYED
            ]);

        }else if($request->type_id == 2){
            $campaign->update([
                'status_id' => CampaignStatus::STATUS_LISTENING
            ]);
        }

        return redirect()->route('automations.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $task = $this->automations->find($workspaceId, $id,['campaign']);
        return view('automations.edit', compact('task'));
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
                'scheduled_at' => $request->scheduled_at,
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
        $workspaceId = MailSystem::currentWorkspaceId();

        $automations = $this->automations->find($workspaceId, $request->get('id'),['campaign']);

        if ($automations->status == AutoTask::STATUS_RUNING) {
            return redirect()->route('automations.index')
                ->withErrors(__('Unable to delete a task that is not in cancel status'));
        }

        $this->automations->destroy($workspaceId, $request->get('id'));

        if(!in_array($automations->campaign->status_id,[CampaignStatus::STATUS_SENT,CampaignStatus::STATUS_QUEUED,CampaignStatus::STATUS_SENDING])){
            $automations->campaign->update([
                 'status_id' => CampaignStatus::STATUS_DRAFT
            ]);
        }

        return redirect()->route('automations.index',['type'=>$automations->from_type])
            ->with('success', __('The task has been successfully deleted'));
    }

}
