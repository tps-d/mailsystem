<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Messages;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\Message;
use App\Models\Workspace;
use App\Models\Subscriber;
use App\Models\EmailService;

use App\Repositories\SubscriberRepository;

use App\Services\Messages\RelayMessage;
use App\Services\Messages\MessageOptions;
use App\Services\Messages\MessageTrackingOptions;
use App\Services\Content\MergeContentService;

use App\Http\Controllers\Controller;

use App\Http\Middleware\MailSendMiddleware;

class DispatchController extends Controller
{

    protected $subscribers;

    protected $relayMessage;

    private $_app_names=[
        'rusuo' => '如梭加速',
        'haiou' => '海鸥加速'
    ];

    public function __construct(
        SubscriberRepository $subscribers,
        RelayMessage $relayMessage
    ) {
        $this->subscribers = $subscribers;
        $this->relayMessage = $relayMessage;

         $this->middleware(MailSendMiddleware::class);
    }

    public function template_subject(){
        return "【{app_name}】最新请求验证码";
    }

    public function template_content(){
        return "尊敬的用户您好：\r\n\r\n 本次您请求的验证码是 {code}，有效期10分钟（请勿泄露）。";
    }

    private function merge_tags($content, $replace){
        
        foreach($replace as $tag => $value){
            $content = str_ireplace('{'.$tag.'}', $value, $content);
        }
        return $content;
    }

    public function send(Request $request,$platform)
    {
        $data = $request->getContent();
        $json_data = json_decode($data,true);
        if(json_last_error() != JSON_ERROR_NONE){
            throw (new HttpResponseException(response()->json(['code' => 1,'error' => 'Invalid requests'], 200)));
        }

        $recipient_email = $request->get('email');
        $app_name = $request->get('app_name');
        $code = $request->get('code');
        $mail_title = $request->get('mail_title');
        $mail_content = $request->get('mail_content');

        $workspace = Workspace::where('name', $platform)->first();
        if(!$workspace){
           throw (new HttpResponseException(response()->json(['code' => 1,'error' => 'Unknown workspace'], 200)));
        }

        
        if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
           throw (new HttpResponseException(response()->json(['code' => 1,'error' => 'Invalid email format'], 200)));
        }

        if(!$app_name){
            $app_name = isset($this->_app_names[$platform]) ? $this->_app_names[$platform] : '';
        }

        $workspace_id = $workspace->id;

        $emailService = EmailService::where('workspace_id',$workspace_id)->first();
        if(!$emailService){
           throw (new HttpResponseException(response()->json(['code' => 1,'error' => 'Unknown email service'], 200)));
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

        $mail_subject = $mail_title ?? $this->template_subject();
        $mail_content = $mail_content ?? $this->template_content();

        $replaces=[
            'app_name' => $app_name,
            'code' => $code
        ];

        $mail_subject = $this->merge_tags($mail_subject, $replaces);
        $mail_content = $this->merge_tags($mail_content, $replaces);

        //return response()->json(['code' => 0,'error'=>''], 200);

        $messageId = $this->send_mail($workspace_id,$emailService,$recipient_email,$mail_subject,$mail_content);
        if(!$messageId){
            throw (new HttpResponseException(response()->json(['error' => '发送失败，请稍后重试'], 200)));
        }

        return response()->json(['code' => 0,'error'=>'','message_id'=>$messageId], 200);

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

        $trackingOptions = (new MessageTrackingOptions())->disable();

        $messageOptions = (new MessageOptions)
            ->setTo($message->recipient_email)
            ->setFromEmail($message->from_email)
            ->setFromName($message->from_name)
            ->setSubject($message->subject)
            ->setTrackingOptions($trackingOptions);

        return $this->relayMessage->handle_mail($mail_content, $messageOptions, $emailService);

    }
}
