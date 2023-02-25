<?php

namespace App\Pipelines\Campaigns;

use App\Events\MessageEmailDispatchEvent;
use App\Events\MessageSocialDispatchEvent;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Subscriber;
use App\Models\SocialUser;

use App\Models\Tag;

class CreateMessages
{
    /**
     * Stores unique subscribers for this campaign
     *
     * @var array
     */
    protected $sentItems = [];

    /**
     * CreateMessages handler
     *
     * @param Campaign $campaign
     * @param $next
     * @return Campaign
     * @throws \Exception
     */
    public function handle(Campaign $campaign, $next)
    {

        if($campaign->is_send_mail){
            if ($campaign->send_to_all) {
                $this->handleAllSubscribers($campaign);
            } else {
                $this->handleTags($campaign);
            }
        }

        if($campaign->is_send_social){
             $this->handleSocialUsers($campaign);
        }

        return $next($campaign);
    }

    /**
     * Handle a campaign where all subscribers have been selected
     *
     * @param Campaign $campaign
     * @throws \Exception
     */
    protected function handleAllSubscribers(Campaign $campaign)
    {
        Subscriber::where('workspace_id', $campaign->workspace_id)
            ->whereNull('unsubscribed_at')
            ->chunkById(1000, function ($subscribers) use ($campaign) {
                $this->dispatchToSubscriber($campaign, $subscribers);
            }, 'id');
    }

    protected function handleSocialUsers(Campaign $campaign)
    {
        SocialUser::where('workspace_id', $campaign->workspace_id)
            ->whereNull('unsubscribed_at')
            ->chunkById(1000, function ($socialusers) use ($campaign) {
                $this->dispatchToSocialUser($campaign, $socialusers);
            }, 'id');
    }

    /**
     * Loop through each tag
     *
     * @param Campaign $campaign
     */
    protected function handleTags(Campaign $campaign)
    {
        foreach ($campaign->tags as $tag) {
            $this->handleTag($campaign, $tag);
        }
    }

    /**
     * Handle each tag
     *
     * @param Campaign $campaign
     * @param Tag $tag
     *
     * @return void
     */
    protected function handleTag(Campaign $campaign, Tag $tag): void
    {
        \Log::info('- Handling Campaign Tag id='.$tag->id);

        $tag->subscribers()->whereNull('unsubscribed_at')->chunkById(1000, function ($subscribers) use ($campaign) {
            $this->dispatchToSubscriber($campaign, $subscribers);
        }, 'sendportal_subscribers.id');
    }

    /**
     * Dispatch the campaign to a given subscriber
     *
     * @param Campaign $campaign
     * @param $subscribers
     */
    protected function dispatchToSubscriber(Campaign $campaign, $subscribers)
    {
        \Log::info('- Number of subscribers in this chunk: ' . count($subscribers));

        foreach ($subscribers as $subscriber) {
            if (! $this->canSendToSubscriber('email',$campaign->id, $subscriber->id)) {
                continue;
            }

            $this->dispatch('mail',$campaign, $subscriber);
        }
    }

    protected function dispatchToSocialUser(Campaign $campaign, $socialusers)
    {
        \Log::info('- Number of socialusers in this chunk: ' . count($socialusers));

        foreach ($socialusers as $socialuser) {
            if (! $this->canSendToSubscriber('social',$campaign->id, $socialuser->id)) {
                continue;
            }

            $this->dispatch('social',$campaign, $socialuser);
        }
    }

    /**
     * Check if we can send to this subscriber
     * @todo check how this would impact on memory with 200k subscribers?
     *
     * @param int $campaignId
     * @param int $subscriberId
     *
     * @return bool
     */
    protected function canSendToSubscriber($type,$campaignId, $subscriberId): bool
    {
        $key = $type. '-' . $campaignId . '-' . $subscriberId;

        if (in_array($key, $this->sentItems, true)) {
            \Log::info('- Subscriber has already been sent a message type=' . $type . ' campaign_id=' . $campaignId . ' subscriber_id=' . $subscriberId);

            return false;
        }

        $this->appendSentItem($key);

        return true;
    }

    /**
     * Append a value to the sentItems
     *
     * @param string $value
     * @return void
     */
    protected function appendSentItem(string $value): void
    {
        $this->sentItems[] = $value;
    }

    /**
     * Dispatch the message
     *
     * @param Campaign $campaign
     * @param Subscriber $subscriber
     */
    /*
    protected function dispatch(Campaign $campaign, $subscriber): void
    {
        //if ($campaign->save_as_draft) {
        //    $this->saveAsDraft($campaign, $subscriber);
        //} else {
            $this->dispatchNow($campaign, $subscriber);
        //}
    }
*/
    /**
     * Dispatch a message now
     *
     * @param Campaign $campaign
     * @param Subscriber $subscriber
     * @return Message
     */
    protected function dispatch($type, Campaign $campaign, $subscriber): Message
    {
        // If a message already exists, then we're going to assume that
        // it has already been dispatched. This makes the dispatch fault-tolerant
        // and prevent dispatching the same message to the same subscriber
        // more than once
        /*
        if ($message = $this->findMessage($campaign, $subscriber)) {
            \Log::info('Message has previously been created campaign=' . $campaign->id . ' subscriber=' . $subscriber->id);

            return $message;
        }*/

        if($type == 'social'){
            // the message doesn't exist, so we'll create and dispatch
            \Log::info('Saving empty social message type=social campaign=' . $campaign->id . ' subscriber=' . $subscriber->id);
            $attributes = [
                'workspace_id' => $campaign->workspace_id,
                'is_send_mail' => false,
                'is_send_social' => true,
                'subscriber_type' => 'social',
                'subscriber_id' => $subscriber->id,
                'source_type' => Campaign::class,
                'source_id' => $campaign->id,
                'recipient_chat_id' => $subscriber->chat_id,
                'recipient_email' => '',
                'subject' => '',
                'from_name' => '',
                'from_email' => '',
                'from_social' => $campaign->social_service->bot_username,
                'queued_at' => now(),
                'sent_at' => null,
            ];

            $message = new Message($attributes);
            $message->save();

            event(new MessageSocialDispatchEvent($message));  
        }else if($type == 'mail'){
            // the message doesn't exist, so we'll create and dispatch
            \Log::info('Saving empty email message type=email campaign=' . $campaign->id . ' subscriber=' . $subscriber->id);
            $attributes = [
                'workspace_id' => $campaign->workspace_id,
                'is_send_mail' => true,
                'is_send_social' => false,
                'subscriber_type' => 'email',
                'subscriber_id' => $subscriber->id,
                'source_type' => Campaign::class,
                'source_id' => $campaign->id,
                'recipient_email' => $subscriber->email,
                'recipient_chat_id' => 0,
                'subject' => $campaign->subject,
                'from_name' => $campaign->email_service->from_name,
                'from_email' => $campaign->email_service->from_email,
                'from_social' => null,
                'queued_at' => now(),
                'sent_at' => null,
            ];

            $message = new Message($attributes);
            $message->save();

            event(new MessageEmailDispatchEvent($message)); 
        }


        return $message;
    }


    /**
     * @param Campaign $campaign
     * @param Subscriber $subscriber
     */
/*    
    protected function saveAsDraft(Campaign $campaign, $subscriber)
    {
        \Log::info('Saving message as draft campaign=' . $campaign->id . ' subscriber=' . $subscriber->id);

        Message::firstOrCreate(
            [
                'workspace_id' => $campaign->workspace_id,
                'subscriber_id' => $subscriber->id,
                'source_type' => Campaign::class,
                'source_id' => $campaign->id,
            ],
            [
                'recipient_email' => $subscriber->email,
                'subject' => $campaign->subject,
                'from_name' => $campaign->from_name,
                'from_email' => $campaign->from_email,
                'queued_at' => now(),
                'sent_at' => null,
            ]
        );
    }
*/

    protected function findMessage(Campaign $campaign, $subscriber): ?Message
    {
        return Message::where('workspace_id', $campaign->workspace_id)
            ->where('subscriber_id', $subscriber->id)
            ->where('is_send_mail', $campaign->is_send_mail)
            ->where('is_send_social', $campaign->is_send_social)
            ->where('source_type', Campaign::class)
            ->where('source_id', $campaign->id)
            ->first();
    }
}
