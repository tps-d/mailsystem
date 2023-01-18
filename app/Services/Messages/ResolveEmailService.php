<?php

namespace App\Services\Messages;

use Exception;
use App\Models\EmailService;
use App\Models\Message;
use App\Repositories\CampaignRepository;
use App\Repositories\AutomationsRepository;

class ResolveEmailService
{
    /** @var CampaignRepository */
    protected $campaignRepository;

    /** @var AutomationsRepository */
    protected $automationsRepository;

    public function __construct(CampaignRepository $campaignRepository,AutomationsRepository $automationsRepository)
    {
        $this->campaignRepository = $campaignRepository;
        $this->automationsRepository = $automationsRepository;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): EmailService
    {
        if ($message->isAutomation()) {
            return $this->resolveAutomationEmailService($message);
        }

        if ($message->isCampaign()) {
            return $this->resolveCampaignEmailService($message);
        }

        throw new Exception('Unable to resolve email service for message id=' . $message->id);
    }

    /**
     * Resolve the email service for an automation
     *
     * @param Message $message
     * @return EmailService
     * @throws Exception
     */
    protected function resolveAutomationEmailService(Message $message): EmailService
    {
        if (! $automations = $this->automationsRepository->find($message->workspace_id, $message->source_id, ['email_service'])) {
            throw new Exception('Unable to resolve automations for message id=' . $message->id);
        }

        if (! $emailService = $automations->email_service) {
            throw new Exception('Unable to resolve email service for message id=' . $message->id);
        }

        return $emailService;
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
}
