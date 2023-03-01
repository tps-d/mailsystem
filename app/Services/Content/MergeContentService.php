<?php

declare(strict_types=1);

namespace App\Services\Content;

use Exception;
use App\Models\Campaign;
use App\Models\Message;
use App\Repositories\CampaignRepository;
use App\Repositories\VariableRepository;
use App\Repositories\AutotriggerRepository;
use App\Traits\NormalizeTags;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

use App\Facades\Helper;
use App\Services\PlatformService;

class MergeContentService
{
    use NormalizeTags;

    /** @var CampaignRepository */
    protected $campaignRepo;

    /** @var AutotriggerRepository */
    protected $autotriggerRepo;

    /** @var VariableRepository */
    protected $variableRepo;

    /** @var CssToInlineStyles */
    protected $cssProcessor;

    protected $platformService;

    public function __construct(
        CampaignRepository $campaignRepo,
        AutotriggerRepository $autotriggerRepo,
        VariableRepository $variableRepo,
        CssToInlineStyles $cssProcessor,
        PlatformService $platformService

    ) {
        $this->campaignRepo = $campaignRepo;
        $this->autotriggerRepo = $autotriggerRepo;
        $this->variableRepo = $variableRepo;
        $this->cssProcessor = $cssProcessor;
        $this->platformService = $platformService;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): string
    {
        return $this->inlineStyles($this->resolveContent($message));
    }

    /**
     * @throws Exception
     */
    protected function resolveContent(Message $message): string
    {

        if(isset($message->template_content)){
            $mergedContent = $message->template_content;
        }else{
            if ($message->isCampaign()) {
                $mergedContent = $this->mergeCampaignContent($message);
            } else if($message->isAutoTrigger()){
                $mergedContent = $this->mergeAutoTriggerContent($message);
            } else {
                throw new Exception('Invalid message source type for message id=' . $message->id);
            }
        }

        return $this->mergeTags($mergedContent, $message);
    }

    /**
     * @throws Exception
     */
    protected function mergeCampaignContent(Message $message): string
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaignRepo->find($message->workspace_id, $message->source_id, ['template']);

        if (!$campaign) {
            throw new Exception('Unable to resolve campaign step for message id= ' . $message->id);
        }

        return $campaign->template
            ? $this->mergeContent($campaign->content, $campaign->template->content)
            : $campaign->content;
    }

    /**
     * @throws Exception
     */
    protected function mergeAutoTriggerContent(Message $message): string
    {
        $autotrigger = $this->autotriggerRepo->find($message->workspace_id, $message->source_id, ['template']);

        if (!$autotrigger) {
            throw new Exception('Unable to resolve autotrigger step for message id= ' . $message->id);
        }

        return $autotrigger->template->content;
    }

    protected function mergeContent(?string $customContent, string $templateContent): string
    {
        return str_ireplace(['{{content}}', '{{ content }}'], $customContent ?: '', $templateContent);
    }

    protected function mergeTags(string $content, Message $message): string
    {
        $content = $this->compileTags($content);

        $content = $this->mergeSubscriberTags($content, $message);
        $content = $this->mergeUnsubscribeLink($content, $message);
        $content = $this->mergeWebviewLink($content, $message);
        $content = $this->mergeSystemTags($content, $message);
        $content = $this->mergeUserTags($content, $message);

        return $content;
    }

    protected function compileTags(string $content): string
    {
        $tags = [
            'email',
            'first_name',
            'last_name',
            'unsubscribe_url',
            'webview_url'
        ];

        foreach ($tags as $tag) {
            $content = $this->normalizeTags($content, $tag);
        }

        return $content;
    }

    protected function mergeSubscriberTags(string $content, Message $message): string
    {
        $tags = [
            'email' => $message->recipient_email ?? '',
            'first_name' => optional($message->subscriber)->first_name ?? '',
            'last_name' => optional($message->subscriber)->last_name ?? ''
        ];

        foreach ($tags as $key => $replace) {
            $content = str_ireplace('{{' . $key . '}}', $replace, $content);
        }

        return $content;
    }

    protected function mergeUnsubscribeLink(string $content, Message $message): string
    {
        $unsubscribeLink = $this->generateUnsubscribeLink($message);

        return str_ireplace(['{{ unsubscribe_url }}', '{{unsubscribe_url}}'], $unsubscribeLink, $content);
    }

    protected function generateUnsubscribeLink(Message $message): string
    {
        return route('subscriptions.unsubscribe', $message->hash);
    }

    protected function mergeWebviewLink(string $content, Message $message): string
    {
        $webviewLink = $this->generateWebviewLink($message);

        return str_ireplace('{{webview_url}}', $webviewLink, $content);
    }

    protected function mergeSystemTags(string $content, Message $message): string
    {
        preg_match_all("/(?:\{)(.*)(?:\})/iU",$content, $result);
        
        $org = $result[0];
        $res = $result[1];
        foreach($res as $index => $tag){
            if(Helper::str_starts_with($tag,'CAPTCHACODE')){
                $tag_p = explode('_', $tag);
                if(count($tag_p) != 3){
                    continue;
                }

                list($tname,$workspace_name,$type) = $tag_p;

                $res = $this->platformService->setPlatform($workspace_name)->getApiCaptcha($message->recipient_email,$type);

                if(!isset($res['code']) || $res['code'] != 200){
                    throw new Exception('Failed getApiCaptcha with '.$message->recipient_email.' for message id= ' . $message->id .': '.json_encode($res));
                }

                $variableContent = $res['data']["captcha"];
                $content = str_ireplace($org[$index], $variableContent ,$content);

            }else if(Helper::str_starts_with($tag,'EXCHANGECODE')){
                $tag_p = explode('_', $tag);
                if(count($tag_p) != 5){
                    continue;
                }

                list($tname,$workspace_name,$postage_id,$type_day,$exp) = $tag_p;
                $remark = "自动发信生成";

                $expire_data = now()->addDays($exp)->toDateTimeString();
                $expire_time = strtotime($expire_data);
                $res = $this->platformService->setPlatform($workspace_name)->createApiCryptCard($postage_id,1,$type_day,1,$expire_time,$remark);

                if(!isset($res['code']) || $res['code'] != 200){
                    throw new Exception('Failed createApiCryptCard with '.$message->recipient_email.' for message id= ' . $message->id .': '.json_encode($res));
                }

                $variableContent = $res['data']["code"][0];
                $content = str_ireplace($org[$index], $variableContent ,$content);
            }
        }

        return $content;
    }

    protected function mergeUserTags(string $content, Message $message): string
    {
        $variables = $this->variableRepo->getCache($message->workspace_id);

        foreach($variables as $tag_name => $variable){
            $vkey = "{".$tag_name."}";
            if(false !== strpos($content, $vkey)){
                $variableContent = $this->variableRepo->flashVariableContent($message->workspace_id,$message->recipient_email,$variable);
                $content = str_ireplace($vkey, $variableContent ,$content);
            }
        }

        return $content;
    }

    protected function generateWebviewLink(Message $message): string
    {
        return route('webview.show', $message->hash);
    }

    protected function inlineStyles(string $content): string
    {
        return $this->cssProcessor->convert($content);
    }
}
