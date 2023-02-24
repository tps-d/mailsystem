<?php

declare(strict_types=1);

namespace App\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\MessageSocialDispatchEvent;
use App\Services\Messages\DispatchSocial;

class MessageSocialDispatchHandler implements ShouldQueue
{
    /** @var string */
    public $queue = 'social-dispatch';

    /** @var DispatchSocial */
    protected $dispatchSocial;

    public function __construct(DispatchSocial $dispatchSocial)
    {
        $this->dispatchSocial = $dispatchSocial;
    }

    /**
     * @throws Exception
     */
    public function handle(MessageSocialDispatchEvent $event): void
    {
        $this->dispatchSocial->handle($event->message);
    }
}
