<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Models\Message;
use App\Repositories\MessageRepository;
use App\Services\Content\MergeContentService;
use App\Services\Content\MergeSubjectService;
use App\Services\Messages\DispatchMessage;
use App\Services\Messages\DispatchSocial;
use App\Facades\MailSystem;

class MessagesController extends Controller
{
    /** @var MessageRepository */
    protected $messageRepo;

    /** @var DispatchMessage */
    protected $dispatchMessage;

    /** @var DispatchSocial */
    protected $dispatchSocial;

    /** @var MergeContentService */
    protected $mergeContentService;

    /** @var MergeSubjectService */
    protected $mergeSubjectService;

    public function __construct(
        MessageRepository $messageRepo,
        DispatchMessage $dispatchMessage,
        DispatchSocial $dispatchSocial,
        MergeContentService $mergeContentService,
        MergeSubjectService $mergeSubjectService
    ) {
        $this->messageRepo = $messageRepo;
        $this->dispatchMessage = $dispatchMessage;
        $this->dispatchSocial = $dispatchSocial;
        $this->mergeContentService = $mergeContentService;
        $this->mergeSubjectService = $mergeSubjectService;
    }

    /**
     * Show all sent messages.
     *
     * @throws Exception
     */
    public function index(): View
    {
        $params = request()->only(['search', 'status','source_id']);
        $params['sent'] = true;

        $messages = $this->messageRepo->paginateWithSource(
            MailSystem::currentWorkspaceId(),
            'sent_atDesc',
            [],
            50,
            $params
        );

        return view('messages.index', compact('messages'));
    }

    /**
     * Show draft messages.
     *
     * @throws Exception
     */
    public function draft(): View
    {
        $params = request()->only(['source_id']);
        $params['draft'] = true;

        $messages = $this->messageRepo->paginateWithSource(
            MailSystem::currentWorkspaceId(),
            'created_atDesc',
            [],
            50,
            $params
        );

        return view('messages.index', compact('messages'));
    }

    /**
     * Show a single message.
     *
     * @throws Exception
     */
    public function show(int $messageId): View
    {
        $message = $this->messageRepo->find(MailSystem::currentWorkspaceId(), $messageId);

        $content = $this->mergeContentService->handle($message);
        $subject = $this->mergeSubjectService->handle($message);

        return view('messages.show', compact('content', 'message', 'subject'));
    }

    /**
     * Send a message.
     *
     * @throws Exception
     */
    public function send(): RedirectResponse
    {

        if (!$message = $this->messageRepo->find(
            MailSystem::currentWorkspaceId(),
            request('id'),
            ['subscriber']
        )) {
            return redirect()->back()->withErrors(__('Unable to locate that message'));
        }

        if ($message->sent_at) {
            return redirect()->back()->withErrors(__('The selected message has already been sent'));
        }

        if($message->is_send_mail){
            $this->dispatchMessage->handle($message);
        }else{
            $this->dispatchSocial->handle($message);
        }
        
        return redirect()->back()->with(
            'success',
            __('The message was sent successfully.')
        );
    }

    /**
     * Send a message.
     *
     * @throws Exception
     */
    public function delete(): RedirectResponse
    {
        if (!$message = $this->messageRepo->find(
            MailSystem::currentWorkspaceId(),
            request('id')
        )) {
            return redirect()->back()->withErrors(__('Unable to locate that message'));
        }

        if ($message->sent_at) {
            return redirect()->back()->withErrors(__('A sent message cannot be deleted'));
        }

        $this->messageRepo->destroy(
            MailSystem::currentWorkspaceId(),
            $message->id
        );

        return redirect()->back()->with(
            'success',
            __('The message was deleted')
        );
    }

    /**
     * Send multiple messages.
     *
     * @throws Exception
     */
    public function sendSelected(): RedirectResponse
    {
        if (! request()->has('messages')) {
            return redirect()->back()->withErrors(__('No messages selected'));
        }

        if (!$messages = $this->messageRepo->getWhereIn(
            MailSystem::currentWorkspaceId(),
            request('messages'),
            ['subscriber']
        )) {
            return redirect()->back()->withErrors(__('Unable to locate messages'));
        }

        $messages->each(function (Message $message) {
            if ($message->sent_at) {
                return;
            }

            $this->dispatchMessage->handle($message);
        });

        return redirect()->route('messages.draft')->with(
            'success',
            __('The messages were sent successfully.')
        );
    }
}
