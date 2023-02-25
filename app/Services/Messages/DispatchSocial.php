<?php

declare(strict_types=1);

namespace App\Services\Messages;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Models\SocialService;
use App\Models\Message;
use App\Services\Content\MergeContentService;
use App\Services\Content\MergeSubjectService;

class DispatchSocial
{
    /** @var ResolveService */
    protected $resolveService;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContentService */
    protected $mergeContentService;

    /** @var MergeSubjectService */
    //protected $mergeSubjectService;

    /** @var MarkAsSent */
    protected $markAsSent;

    public function __construct(
        MergeContentService $mergeContentService,
       // MergeSubjectService $mergeSubjectService,
        ResolveSocialService $resolveSocialService,
        RelayMessage $relayMessage,
        MarkAsSent $markAsSent
    ) {
        $this->mergeContentService = $mergeContentService;
       // $this->mergeSubjectService = $mergeSubjectService;
        $this->resolveSocialService = $resolveSocialService;
        $this->relayMessage = $relayMessage;
        $this->markAsSent = $markAsSent;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): ?string
    {
        if (!$this->isValidMessage($message)) {
            Log::info('Message is not valid, skipping id=' . $message->id);

            return null;
        }

       // $message = $this->mergeSubject($message);

        $mergedContent = $this->getMergedContent($message);

        $socialService = $this->getSocialService($message);

        $messageId = $this->dispatch($message, $socialService, $mergedContent);

        $this->markSent($message, $messageId);

        return $messageId;
    }

    /**
     * The message's subject is merged and persisted to the database
     * so that we have a permanent record of the merged tags at the
     * time of dispatch.
     */
    protected function mergeSubject(Message $message): Message
    {
        $message->subject = $this->mergeSubjectService->handle($message);
        $message->save();

        return $message;
    }

    /**
     * @throws Exception
     */
    protected function getMergedContent(Message $message): string
    {
        return $this->mergeContentService->handle($message);
    }

    /**
     * @throws Exception
     */
    protected function dispatch(Message $message, SocialService $socialService,string $mergedContent): ?string
    {
        $messageOptions = (new MessageOptions)
            ->setTo((string)$message->recipient_chat_id);

        $messageId = $this->relayMessage->handle_social($mergedContent, $messageOptions, $socialService);

        Log::info('Message has been dispatched.', ['message_id' => $messageId]);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getSocialService(Message $message): SocialService
    {
        return $this->resolveSocialService->handle($message);
    }

    protected function markSent(Message $message, string $messageId): Message
    {
        return $this->markAsSent->handle($message, $messageId);
    }

    protected function isValidMessage(Message $message): bool
    {
        if ($message->sent_at) {
            return false;
        }

        if (!$message->isCampaign()) {
            return true;
        }

        $campaign = Campaign::find($message->source_id);

        if (!$campaign) {
            return false;
        }

        return $campaign->status_id !== CampaignStatus::STATUS_CANCELLED;
    }
}
