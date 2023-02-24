<?php

declare(strict_types=1);

namespace App\Adapters;

use Illuminate\Support\Arr;
use Telegram\Bot\Api;

class TelegramSocialAdapter extends BaseSocialAdapter
{
    protected $client;

    public function send( $chat_id, string $content)
    {
        $parameters = [
            'chat_id' => $chat_id,
            'text' => $this->_parse_content($content)
        ];

        $response = $this->resolveClient()->sendMessage($parameters);

        return (string)$response->getMessageId();
    }

    protected function resolveClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Api(Arr::get($this->config, 'token'));
        return $this->client;
    }

    private function _parse_content($content){
        return strip_tags($content);
    }
}
