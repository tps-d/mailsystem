<?php

declare(strict_types=1);

namespace App\Services\Content;

use Exception;
use App\Models\Campaign;
use App\Models\Message;
use App\Repositories\CampaignRepository;
use App\Repositories\VariableRepository;
use App\Repositories\AutomationsRepository;
use App\Traits\NormalizeTags;
use Sendportal\Pro\Repositories\AutomationScheduleRepository;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class MergeContentService
{
    use NormalizeTags;

    /** @var CampaignRepository */
    protected $campaignRepo;

    /** @var AutomationsRepository */
    protected $automationsRepo;

    /** @var VariableRepository */
    protected $variableRepo;

    /** @var CssToInlineStyles */
    protected $cssProcessor;

    public function __construct(
        CampaignRepository $campaignRepo,
        AutomationsRepository $automationsRepo,
        VariableRepository $variableRepo,
        CssToInlineStyles $cssProcessor
    ) {
        $this->campaignRepo = $campaignRepo;
        $this->automationsRepo = $automationsRepo;
        $this->variableRepo = $variableRepo;
        $this->cssProcessor = $cssProcessor;
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

        if ($message->isCampaign()) {
            $mergedContent = $this->mergeCampaignContent($message);
        } elseif ($message->isAutomation()) {
            $mergedContent = $this->mergeAutomationContent($message);
        } else {
            throw new Exception('Invalid message source type for message id=' . $message->id);
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
    protected function mergeAutomationContent(Message $message): string
    {
        $automations = $this->automationsRepo->find($message->workspace_id, $message->source_id, ['template']);

        if (!$automations) {
            throw new Exception('Unable to resolve automations step for message id= ' . $message->id);
        }

        return $automations->template
            ? $this->mergeContent($automations->content, $automations->template->content)
            : $automations->content;

        return $this->mergeContent($content, $template->content);
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
            'email' => $message->recipient_email,
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
