<?php

declare(strict_types=1);

namespace App\Services\Messages;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Campaign;
use App\Models\SocialService;
use App\Models\Message;
use App\Repositories\CampaignRepository;
use App\Services\Content\MergeContentService;

class DispatchTestSocialMessage
{

    protected $resolveService;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContentService */
    protected $mergeContent;

    /** @var CampaignRepository */
    protected $campaignTenant;

    public function __construct(
        CampaignRepository $campaignTenant,
        MergeContentService $mergeContent,
        ResolveSocialService $resolveService,
        RelayMessage $relayMessage
    ) {
        $this->resolveService = $resolveService;
        $this->relayMessage = $relayMessage;
        $this->mergeContent = $mergeContent;
        $this->campaignTenant = $campaignTenant;
    }

    /**
     * @throws Exception
     */
    public function handle(int $workspaceId, int $campaignId, $recipientChatId): ?string
    {
        $campaign = $this->resolveCampaign($workspaceId, $campaignId);

        if (!$campaign) {
            Log::error(
                'Unable to get campaign to send test message.',
                ['workspace_id' => $workspaceId, 'campaign_id' => $campaignId]
            );
            return null;
        }

        $message = $this->createTestMessage($campaign, $recipientChatId);

        $mergedContent = $this->getMergedContent($message);

        $socialService = $this->getSocialService($message);

        return $this->dispatch($message, $socialService, $mergedContent);
    }

    /**
     * @throws Exception
     */
    public function testService(int $workspaceId, SocialService $socialService, MessageOptions $options): ?string
    {
        $message = new Message([
            'workspace_id' => $workspaceId,
            'recipient_chat_id' => $options->getTo(),
            'subject' => '',
            'from_name' => '',
            'from_email' => '',
            'hash' => 'abc123',
        ]);

        return $this->dispatch($message, $socialService,$options->getBody());
    }

    /**
     * @throws Exception
     */
    protected function resolveCampaign(int $workspaceId, int $campaignId): ?Campaign
    {
        return $this->campaignTenant->find($workspaceId, $campaignId,['social_service']);
    }

    /**
     * @throws Exception
     */
    protected function getMergedContent(Message $message): string
    {
        return $this->mergeContent->handle($message);
    }

    /**
     * @throws Exception
     */
    protected function dispatch(Message $message, SocialService $socialService, string $mergedContent): ?string
    {
        $messageOptions = (new MessageOptions)->setTo($message->recipient_chat_id);

        $messageId = $this->relayMessage->handle_social($mergedContent, $messageOptions, $socialService);

        Log::info('Message has been dispatched.', ['message_id' => $messageId]);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getSocialService(Message $message): SocialService
    {
        return $this->resolveService->handle($message);
    }

    protected function createTestMessage(Campaign $campaign, $recipientChatId): Message
    {
        return new Message([
            'workspace_id' => $campaign->workspace_id,
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'recipient_chat_id' => $recipientChatId,
            'subject' => '[Test]',
            'from_name' => '',
            'from_email' => '',
            'hash' => 'abc123',
        ]);
    }
}
