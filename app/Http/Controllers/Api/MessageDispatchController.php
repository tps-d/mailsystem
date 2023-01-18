<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\View\View;

use App\Models\Message;
use App\Models\Automations;
use App\Models\Template;
use App\Models\EmailService;
use App\Models\CampaignStatus;
use App\Repositories\MessageRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\SubscriberRepository;
use App\Repositories\AutomationsRepository;
use App\Services\Messages\DispatchMessage;
use App\Facades\MailSystem;
use App\Http\Controllers\Controller;

use App\Http\Requests\Api\MessageDispatchRequest;
use App\Http\Resources\MessageDispatch as MessageDispatchResource;

class MessageDispatchController extends Controller
{
    /** @var TemplateRepository */
    protected $templateRepo;

    /** @var MessageRepository */
    protected $messageRepo;

    protected $subscribers;

    /** @var DispatchMessage */
    protected $dispatchMessage;

    public function __construct(
        TemplateRepository $templateRepo,
        MessageRepository $messageRepo,
        SubscriberRepository $subscribers,
        DispatchMessage $dispatchMessage
    ) {
        $this->templateRepo = $templateRepo;
        $this->messageRepo = $messageRepo;
        $this->subscribers = $subscribers;
        $this->dispatchMessage = $dispatchMessage;
    }

    /**
     * Send a message.
     *
     * @throws Exception
     */
    public function send(MessageDispatchRequest $request)
    {
        $reqs = $request->validated();


        $recipient_email = $reqs['email'] ?? null;
        if(!filter_var($recipient_email,FILTER_VALIDATE_EMAIL)){
            return response([ 'message' => __('Invalid email address') ], 422);
        }

        $workspaceId = MailSystem::currentWorkspaceId();

        $template = $this->templateRepo->findBy( $workspaceId, 'template_label', $reqs['template_label'] );
        if (!$template) {
            return response([ 'message' => __('Unknow template') ], 422);
        }

        $subscriber_data = [
            'workspace_id' => $workspaceId,
            'email' => $recipient_email
        ];
        $subscriber = $this->subscribers->findBy($workspaceId, 'email', $subscriber_data['email']);
        if (!$subscriber) {
            $subscriber = $this->subscribers->store($workspaceId, $subscriber_data);
        }

        $emailService = EmailService::where('workspace_id',$workspaceId)->first();
        if(!$emailService){
            return response([ 'message' => __('no email service supported.') ], 422);
        }

        $automations = $this->createAutomations($workspaceId,$emailService,$template);
        $message = $this->createAutoMessage($workspaceId,$automations,$subscriber);

        $this->dispatchMessage->handle($message);

        return new MessageDispatchResource($message);
    }


    protected function createAutomations($workspace_id, EmailService $emailService, Template $template): Automations
    {

        $from_email = $emailService->name;
        $email_ps =explode('@', $from_email);
        $from_name = $email_ps[0];

        $attributes = [
            'workspace_id' => $workspace_id,
            'name' => "[ API SEND ] ".$template->name,
            'status_id' => CampaignStatus::STATUS_SENT,
            'template_id' => $template->id,
            'email_service_id'  => $emailService->id,
            'subject' => $template->name,
            'content' => null,
            'from_name' => $from_name,
            'from_email' => $from_email,
            'is_open_tracking' => true,
            'is_click_tracking' => true,
            'send_to_all' => 0,
            'save_as_draft' => 0,
            'scheduled_at' => now()
        ];

        $automations = new Automations($attributes);
        $automations->save();

        return $automations;
    }


    protected function createAutoMessage($workspace_id, Automations $automations, $subscriber): Message
    {
        $attributes = [
            'workspace_id' => $workspace_id,
            'subscriber_id' => $subscriber->id,
            'source_type' => Automations::class,
            'source_id' => $automations->id,
            'recipient_email' => $subscriber->email,
            'subject' => $automations->name,
            'from_name' => $automations->from_name,
            'from_email' => $automations->from_email,
            'queued_at' => null,
            'sent_at' => null,
        ];

        $message = new Message($attributes);
        $message->save();

        return $message;
    }
}
