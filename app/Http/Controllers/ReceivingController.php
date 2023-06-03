<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\ReceivingNotifyRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AutoTrigger;
use App\Models\Template;
use App\Models\EmailService;
use App\Models\SocialService;
use App\Models\SocialUser;
use App\Models\Subscriber;
use App\Models\Message;
use App\Models\Campaign;
use App\Services\Messages\MessageOptions;
use App\Services\Messages\MessageTrackingOptions;
use App\Services\Messages\RelayMessage;
use App\Services\Content\MergeContentService;

use App\Facades\MailSystem;

use App\Repositories\SocialUsersRepository;
use App\Repositories\SubscriberRepository;

use \Telegram\Bot\Api;
use \Telegram\Bot\Exceptions\TelegramSDKException;

class ReceivingController extends Controller
{
    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContentService */
    protected $mergeContent;

    /** @var SubscriberRepository */
    protected $subscriber;

    /** @var SocialUsersRepository */
    protected $socialuser;

    public $log;

    public function __construct(
        MergeContentService $mergeContent,
        RelayMessage $relayMessage,
        SubscriberRepository $subscriber,
        SocialUsersRepository $socialUser
    )
    {
        $this->relayMessage = $relayMessage;
        $this->mergeContent = $mergeContent;
        $this->subscriber = $subscriber;
        $this->socialUser = $socialUser;

    }

    public function notify(Request $request)
    {

        $log = Log::build([
          'driver' => 'single',
          'path' => storage_path('logs/receive_message.log'),
        ]);

        $log->info("Request notify", $request->all());

        $to_email_str = $request->get('To') ?? $request->get('recipient');
        if(!$to_email_str){
            $to_email_str = $request->get('recipient');
        }

        $from_email = $request->get('from');
        $sender_email = $request->get('sender');
        $subject = $request->get('Subject');
        $body_plain = $request->get('body-plain');
        $message_id = $request->get('In-Reply-To');
        $timestamp = $request->get('timestamp');

        if(!$to_email_str){
            $log->info('no to email found');
            return 'ok';
        }

        preg_match("/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}/", $to_email_str, $matches);

        $to_email = isset($matches[0]) ? $matches[0] : '';
        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            $log->info('Invalid email from string '.$to_email_str);
            return 'ok';
        }


        $emailService = EmailService::where('from_email',$to_email)->first();
        if(!$emailService){
            $log->info('no know email service for from_email '.$to_email);
            return 'ok';
        }

        $workspace_id = $emailService->workspace_id;

        $userCount = Subscriber::where('workspace_id',$workspace_id)->where('email',$sender_email)->count();
        if(!$userCount){
            $from = explode(' ', $from_email);
            $this->subscriber->store($workspace_id,[
                'email' => $sender_email,
                'first_name' => $from[0],
                'last_name' => null,
                'unsubscribed_at' => null,
                'unsubscribe_event_id' => null
            ]);
        }

        $autoTrigger = AutoTrigger::where('workspace_id',$workspace_id)->where('from_type','email')->where('from_id',$emailService->id)->where('status_id',AutoTrigger::STATUS_ACTIVE)->first();
        if(!$autoTrigger){
            $log->info('no trigger set for from_email '.$to_email .' with workspace_id '.$workspace_id .', from_type email, from_id '.$emailService->id);
            return 'ok';
        }

        if($autoTrigger->condition == 'include'){

            $match_content = str_replace(["，", "｜", "；","|", ";"], ",", $autoTrigger->match_content);
            $arr = explode(",", $match_content);

            $matched = false;
            foreach($arr as $str){
                if(strpos($str, $body_plain) !== false){
                    $matched = true;
                    break;
                }
            }

            if(!$matched){
                $log->info('no match to content "'.$autoTrigger->match_content.'" with from_email '.$to_email .' with workspace_id '.$workspace_id);
                return 'ok';
            }
        }

        $message = new Message([
            'workspace_id' => $workspace_id,
            'is_send_mail' => true,
            'is_send_social' => false,
            'subscriber_type' => 'email',
            'source_type' => AutoTrigger::class,
            'source_id' => $autoTrigger->id,
            'recipient_email' => $sender_email,
            'subject' => 'Re:' . $subject,
            'from_name' => $emailService->from_name,
            'from_email' => $emailService->from_email,
            'hash' => md5($sender_email)
        ]);;

        $mergedContent = $this->mergeContent->handle($message);

        $trackingOptions = (new MessageTrackingOptions())->disable();


        $messageOptions = (new MessageOptions)
            ->setTo($message->recipient_email)
            ->setFromEmail($message->from_email)
            ->setFromName($message->from_name)
            ->setSubject($message->subject)
            ->setTrackingOptions($trackingOptions);

        $messageId = $this->relayMessage->handle_mail($mergedContent, $messageOptions, $emailService);

        if(!$messageId){
            $log->info('Failed to dispatch email to '.$from_email .' with workspace_id '.$workspace_id);
        }

        return 'ok';
        
    }

    public function telegram_notify(Request $request,$token){


        $log = Log::build([ 'driver' => 'single', 'path' => storage_path('logs/tg.log')]);

        try{
            $telegram = new Api($token);
            $response = $telegram->getMe();
        }catch(TelegramSDKException $e){
            return $e->getMessage();
        }

        $bot_id = $response->getId();
        
        $update = \Telegram::commandsHandler(true);
        $log->info($update);

        $message_id = $update->message->message_id;
        $from = $update->message->from;
        $chat = $update->message->chat;
        $text = $update->message->text;

        $socialService = SocialService::where('bot_id',$bot_id)->first();
        if(!$socialService){
            $log->info('no know social service for bot_id '.$bot_id);
            return 'ok';
        }

        $userCount = SocialUser::where('workspace_id',$socialService->workspace_id)->where('chat_id',$chat->id)->count();
        if(!$userCount){
            $this->socialUser->store($socialService->workspace_id,[
                'chat_id' => $chat->id,
                'first_name' => $from->first_name,
                'last_name' => $from->last_name,
                'username' => $from->username,
                'is_bot' => $from->is_bot,
                'unsubscribed_at' => null,
                'unsubscribe_event_id' => null
            ]);
        }

        $autoTrigger = AutoTrigger::where('workspace_id',$socialService->workspace_id)->where('from_type','social')->where('from_id',$socialService->id)->where('status_id',AutoTrigger::STATUS_ACTIVE)->first();
        if(!$autoTrigger){
            $log->info('no trigger set for bot_id '.$bot_id);
            return 'ok';
        }

        if($autoTrigger->condition == 'include'){
            $match_content = str_replace(["，", "｜", "；","|", ";"], ",", $autoTrigger->match_content);
            $arr = explode(",", $match_content);

            $matched = false;
            foreach($arr as $str){
                if(strpos($str, $text) !== false){
                    $matched = true;
                    break;
                }
            }

            if(!$matched){
                $log->info('no match to content "'.$autoTrigger->match_content.'" with bot_id '.$bot_id);
                return 'ok';
            }

        }

        $message = new Message([
            'workspace_id' => $socialService->workspace_id,
            'recipient_chat_id' => (string)$chat->id,
            'source_type' => AutoTrigger::class,
            'source_id' => $autoTrigger->id,
            'subject' => '',
            'from_name' => '',
            'from_email' => '',
            'hash' => 'abc123',
        ]);

        $mergedContent = $this->mergeContent->handle($message);

        $messageOptions = (new MessageOptions)->setTo($message->recipient_chat_id);
        $messageId = $this->relayMessage->handle_social($mergedContent, $messageOptions, $socialService);

        if(!$messageId){
            $log->info('Failed to dispatch social to '.$chat->id);
        }

        return 'ok';
    }

}
