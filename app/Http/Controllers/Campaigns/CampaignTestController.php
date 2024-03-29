<?php

declare(strict_types=1);

namespace App\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Http\RedirectResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignTestEmailRequest;
use App\Http\Requests\CampaignTestSocialRequest;
use App\Services\Messages\DispatchTestMessage;
use App\Services\Messages\DispatchTestSocialMessage;

use Telegram\Bot\Exceptions\TelegramResponseException;

class CampaignTestController extends Controller
{
    /** @var DispatchTestMessage */
    protected $dispatchTestMessage;

    /** @var DispatchTestMessage */
    protected $dispatchTestSocialMessage;

    public function __construct(DispatchTestMessage $dispatchTestMessage,DispatchTestSocialMessage $dispatchTestSocialMessage)
    {
        $this->dispatchTestMessage = $dispatchTestMessage;
        $this->dispatchTestSocialMessage = $dispatchTestSocialMessage;
    }

    /**
     * @throws Exception
     */
    public function handle_mail(CampaignTestEmailRequest $request, int $campaignId): RedirectResponse
    {
        try{
            $messageId = $this->dispatchTestMessage->handle(0, $campaignId, $request->get('recipient_email'));
        }catch(TelegramSDKException $e){
            return redirect()->route('campaigns.preview', $campaignId)
                ->withInput()
                ->with(['error', $e->getMessage()]);
        }

        if (!$messageId) {
            return redirect()->route('campaigns.preview', $campaignId)
                ->withInput()
                ->with(['error', __('Failed to dispatch test email.')]);
        }

        return redirect()->route('campaigns.preview', $campaignId)
            ->withInput()
            ->with(['success' => __('The test email has been dispatched.')]);
    }

    public function handle_social(CampaignTestSocialRequest $request, int $campaignId): RedirectResponse
    {
        try{
            $messageId = $this->dispatchTestSocialMessage->handle(0, $campaignId, $request->get('recipient_chat_id'));
        }catch(TelegramResponseException $e){
       
            return redirect()->route('campaigns.preview', $campaignId)
                ->withErrors($e->getMessage());
   
        }
        

        if (!$messageId) {
            return redirect()->route('campaigns.preview', $campaignId)
                ->withInput()
                ->with(['error', __('Failed to dispatch test message.')]);
        }

        return redirect()->route('campaigns.preview', $campaignId)
            ->withInput()
            ->with(['success' => __('The test message has been dispatched.')]);
    }
}
