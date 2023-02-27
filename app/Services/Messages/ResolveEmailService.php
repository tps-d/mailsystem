<?php

namespace App\Services\Messages;

use Exception;
use App\Models\EmailService;
use App\Models\Message;
use App\Repositories\CampaignRepository;
use App\Repositories\AutotriggerRepository;

class ResolveEmailService
{
    /** @var CampaignRepository */
    protected $campaignRepository;

    /** @var AutotriggerRepository */
    protected $autotriggerRepository;

    public function __construct(CampaignRepository $campaignRepository,AutotriggerRepository $autotriggerRepository)
    {
        $this->campaignRepository = $campaignRepository;
        $this->autotriggerRepository = $autotriggerRepository;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): EmailService
    {
        if ($message->isCampaign()) {
            return $this->resolveCampaignEmailService($message);
        }else if($message->isAutoTrigger()){
            return $this->resolveAutoTriggerEmailService($message);
        }

        throw new Exception('Unable to resolve email service for message id=' . $message->id);
    }

    /**
     * Resolve the provider for a campaign
     *
     * @param Message $message
     * @return EmailService
     * @throws Exception
     */
    protected function resolveCampaignEmailService(Message $message): EmailService
    {
        if (! $campaign = $this->campaignRepository->find($message->workspace_id, $message->source_id, ['email_service'])) {
            throw new Exception('Unable to resolve campaign for message id=' . $message->id);
        }

        if (! $emailService = $campaign->email_service) {
            throw new Exception('Unable to resolve email service for message id=' . $message->id);
        }

        return $emailService;
    }

    protected function resolveAutoTriggerEmailService(Message $message): EmailService
    {
        if (! $autotrigger = $this->autotriggerRepository->find($message->workspace_id, $message->source_id, ['email_service'])) {
            throw new Exception('Unable to resolve autotrigger for message id=' . $message->id);
        }

        if (! $emailService = $autotrigger->email_service) {
            throw new Exception('Unable to resolve email service for message id=' . $message->id);
        }

        return $emailService;
    }
}
