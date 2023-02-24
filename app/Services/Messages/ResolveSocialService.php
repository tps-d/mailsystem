<?php

namespace App\Services\Messages;

use Exception;
use App\Models\SocialService;
use App\Models\Message;
use App\Repositories\CampaignRepository;

class ResolveSocialService
{
    /** @var CampaignRepository */
    protected $campaignRepository;

    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): SocialService
    {
        if ($message->isCampaign()) {
            return $this->resolveCampaignSocialService($message);
        }

        throw new Exception('Unable to resolve email service for message id=' . $message->id);
    }

    /**
     * Resolve the provider for a campaign
     *
     * @param Message $message
     * @return SocialService
     * @throws Exception
     */
    protected function resolveCampaignSocialService(Message $message): SocialService
    {
        if (! $campaign = $this->campaignRepository->find($message->workspace_id, $message->source_id, ['social_service'])) {
            throw new Exception('Unable to resolve campaign for message id=' . $message->id);
        }

        if (! $socialService = $campaign->social_service) {
            throw new Exception('Unable to resolve social service for message id=' . $message->id);
        }

        return $socialService;
    }
}
