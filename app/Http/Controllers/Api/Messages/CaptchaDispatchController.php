<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Messages;

use Exception;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\Workspace;
use App\Models\Message;
use App\Models\Subscriber;
use App\Models\EmailService;

use App\Services\Messages\RelayMessage;
use App\Services\Messages\MessageOptions;
use App\Services\Messages\MessageTrackingOptions;
use App\Services\Content\MergeContentService;

use App\Repositories\SubscriberRepository;

use App\Facades\MailSystem;
use App\Http\Controllers\Controller;

use App\Http\Requests\Api\CaptchaDispatchRequest;
use App\Http\Resources\MessageDispatch as MessageDispatchResource;

class CaptchaDispatchController extends Controller
{
    protected $subscribers;

    protected $relayMessage;

    protected $mergeContent;

    public $type_titles = [
        0 => '注册',
        1 => '密码找回'
    ];

    public function __construct(
        SubscriberRepository $subscribers,
        RelayMessage $relayMessage,
        MergeContentService $mergeContent
    ) {
        $this->subscribers = $subscribers;
        $this->relayMessage = $relayMessage;
        $this->mergeContent = $mergeContent;
    }

    public function template_subject($workspace, $type = 0){
        return $type ? '账号密码找回验证码' : '新用户注册验证码';
    }

    public function template_content($workspace, $type = 0){

        $tag = "{CAPTCHACODE_".$workspace->name."_".$type."}";
        return "尊敬的用户您好：\r\n\r\n 本次您请求的验证码是".$tag."，有效期10分钟（请勿泄露）。祝您使用愉快！";
    }

    public function send(CaptchaDispatchRequest $request)
    {
        $recipient_email = $request->get('email');
        $type = $request->get('type');

        $workspace_id = MailSystem::currentWorkspaceId();

        $workspace = Workspace::where('id', $workspace_id)->first();
        if(!$workspace){
           throw (new HttpResponseException(response()->json(['error' => 'Unknown workspace'], 200)));
        }

        $emailService = EmailService::where('workspace_id',$workspace_id)->first();
        if(!$emailService){
           throw (new HttpResponseException(response()->json(['error' => 'Unknown email service'], 200)));
        }

        $userCount = Subscriber::where('workspace_id',$workspace_id)->where('email',$recipient_email )->count();
        if(!$userCount){
            $from = explode('@', $recipient_email);
            $this->subscribers->store($workspace_id ,[
                'email' => $recipient_email,
                'first_name' => $from[0],
                'last_name' => null,
                'unsubscribed_at' => null,
                'unsubscribe_event_id' => null
            ]);
        }

        $mail_subject = $this->template_subject($workspace,$type);
        $mail_content = $this->template_content($workspace,$type);

        $messageId = $this->send_mail($workspace_id,$emailService,$recipient_email,$mail_subject,$mail_content);
        if(!$messageId){
            throw (new HttpResponseException(response()->json(['error' => '发送失败，请稍后重试'], 200)));
        }

        return new MessageDispatchResource($request);
    }

    public function send_mail($workspace_id,$emailService,$recipient_email,$mail_subject,$mail_content){

        $message = new Message([
            'workspace_id' => $workspace_id,
            'is_send_mail' => true,
            'is_send_social' => false,
            'subscriber_type' => 'email',
            'source_type' => stdClass::class,
            'source_id' => 0,
            'recipient_email' => $recipient_email,
            'subject' => $mail_subject,
            'from_name' => $emailService->from_name,
            'from_email' => $emailService->from_email,
            'template_content' => $mail_content,
            'hash' => md5($recipient_email)
        ]);

        try{
            $mergedContent = $this->mergeContent->handle($message);
        }catch(\Exception $e){
            if($e->getCode()){
                throw (new HttpResponseException(response()->json(['error' => $e->getMessage()], 200)));
            }else{
                return 0;
            }
        }

        $trackingOptions = (new MessageTrackingOptions())->disable();

        $messageOptions = (new MessageOptions)
            ->setTo($message->recipient_email)
            ->setFromEmail($message->from_email)
            ->setFromName($message->from_name)
            ->setSubject($message->subject)
            ->setTrackingOptions($trackingOptions);

        return $this->relayMessage->handle_mail($mergedContent, $messageOptions, $emailService);


    }
}
