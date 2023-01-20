<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\View\View;

use App\Models\Message;
use App\Models\Template;
use App\Models\Campaign;
use App\Models\EmailService;
use App\Models\CampaignStatus;
use App\Repositories\MessageRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\SubscriberRepository;
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
    public function send(MessageDispatchRequest $request, $campaignId)
    {
        $reqs = $request->validated();

        $workspaceId = MailSystem::currentWorkspaceId();

        $campaign = $request->getCampaign(['email_service']);

        $subscriber_data = [
            'email' => $reqs['email']
        ];

        $subscriber = $this->subscribers->findBy($workspaceId, 'email', $subscriber_data['email']);
        if (!$subscriber) {
            $subscriber = $this->subscribers->store($workspaceId, $subscriber_data);
        }


        $message = $this->createAutoMessage($workspaceId,$campaign,$subscriber);

        $this->dispatchMessage->handle($message);

        return new MessageDispatchResource($message);
    }

    protected function createAutoMessage($workspace_id, Campaign $campaign, $subscriber): Message
    {
        $attributes = [
            'workspace_id' => $workspace_id,
            'subscriber_id' => $subscriber->id,
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'recipient_email' => $subscriber->email,
            'subject' => $campaign->name,
            'from_name' => $campaign->from_name,
            'from_email' => $campaign->from_email,
            'queued_at' => null,
            'sent_at' => null,
        ];

        $message = new Message($attributes);
        $message->save();

        return $message;
    }
}
