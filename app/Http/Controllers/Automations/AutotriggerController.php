<?php

declare(strict_types=1);

namespace App\Http\Controllers\Automations;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutotriggerStoreRequest;
use App\Models\EmailService;
use App\Models\AutoTrigger;
use App\Repositories\AutotriggerRepository;
use App\Repositories\EmailServiceRepository;
use App\Repositories\TemplateRepository;
use App\Facades\MailSystem;

class AutotriggerController extends Controller
{
    /** @var AutotriggerRepository */
    protected $autotrigger;

    /** @var TemplateRepository */
    protected $templates;

    /** @var EmailServiceRepository */
    protected $emailServices;


    public function __construct(
        AutotriggerRepository $autotrigger,
        TemplateRepository $templates,
        EmailServiceRepository $emailServices
    ) {
        $this->autotrigger = $autotrigger;
        $this->templates = $templates;
        $this->emailServices = $emailServices;
    }

    /**
     * @throws Exception
     */
    public function index($type): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $params = ['from_type' => $type];
        $autotriggers = $this->autotrigger->paginate($workspaceId, 'created_atDesc', ['template'], 25, $params);

        return view('autotriggers.index', [
            'autotriggers' => $autotriggers,
            'type' => $type
        ]);
    }

    /**
     * @throws Exception
     */
    public function create($type): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $templates = [null => '- None -'] + $this->templates->pluck($workspaceId);
        $fromOptions = [null => '- None -'] + $this->emailServices->all($workspaceId, 'id', ['type'])
            ->map(static function (EmailService $emailService) {
                $emailService->formatted_name = "{$emailService->from_name} <{$emailService->from_email}> ({$emailService->type->name})";
                return $emailService;
            })->pluck('formatted_name', 'id')->all();

        return view('autotriggers.create', compact('templates', 'fromOptions', 'type'));
    }

    /**
     * @throws Exception
     */
    public function store(AutotriggerStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $autotrigger = $this->autotrigger->store($workspaceId, $request->validated());

        return redirect()->route('autotrigger.index',['type'=>$autotrigger->from_type]);
    }


    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $autotrigger = $this->autotrigger->find($workspaceId, $id);
        $templates = [null => '- None -'] + $this->templates->pluck($workspaceId);
        $fromOptions = [null => '- None -'] + $this->emailServices->all($workspaceId, 'id', ['type'])
            ->map(static function (EmailService $emailService) {
                $emailService->formatted_name = "{$emailService->from_name} <{$emailService->from_email}> ({$emailService->type->name})";
                return $emailService;
            })->pluck('formatted_name', 'id')->all();

        return view('autotriggers.edit', compact('autotrigger', 'fromOptions', 'templates'));
    }

    /**
     * @throws Exception
     */
    public function update(int $id, AutotriggerStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $autotrigger = $this->autotrigger->update(
            $workspaceId,
            $id,
            $request->validated()
        );

        return redirect()->route('autotrigger.index',['type'=>$autotrigger->from_type]);
    }

    public function cancel(int $id)
    {
        $autotrigger = $this->autotrigger->find(MailSystem::currentWorkspaceId(), $id);

        if ($autotrigger->status_id == AutoTrigger::STATUS_CANCELLED) {
            return redirect()->route('autotrigger.index',['type'=>$autotrigger->from_type]);
        }

        $this->autotrigger->cancelAutotrigger($autotrigger);

        return redirect()->route('autotrigger.index',['type'=>$autotrigger->from_type])->with([
            'success' => 'The trigger was cancelled successfully.',
        ]);
    }

    public function active(int $id)
    {
        $autotrigger = $this->autotrigger->find(MailSystem::currentWorkspaceId(), $id);

        if ($autotrigger->status_id == AutoTrigger::STATUS_ACTIVE) {
            return redirect()->route('autotrigger.index',['type'=>$autotrigger->from_type]);
        }

        $this->autotrigger->activeAutotrigger($autotrigger);

        return redirect()->route('autotrigger.index',['type'=>$autotrigger->from_type])->with([
            'success' => 'The trigger was actived successfully.',
        ]);
    }

    public function confirm(int $id)
    {
        $autotrigger = $this->autotrigger->find(MailSystem::currentWorkspaceId(), $id);

        if ($autotrigger->status == AutoTrigger::STATUS_ACTIVE) {
            return redirect()->route('autotrigger.index')
                ->withErrors(__('Unable to delete a autotrigger that is not in cancel status'));
        }

        return view('autotriggers.delete', compact('autotrigger'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $autotrigger = $this->autotrigger->find(MailSystem::currentWorkspaceId(), $request->get('id'));

        if ($autotrigger->status == AutoTrigger::STATUS_ACTIVE) {
            return redirect()->route('autotrigger.index')
                ->withErrors(__('Unable to delete a autotrigger that is not in cancel status'));
        }

        $this->autotrigger->destroy(MailSystem::currentWorkspaceId(), $request->get('id'));

        return redirect()->route('autotrigger.index',['type'=>$autotrigger->from_type])
            ->with('success', __('The autotrigger has been successfully deleted'));
    }
}
