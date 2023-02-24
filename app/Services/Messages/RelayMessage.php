<?php

declare(strict_types=1);

namespace App\Services\Messages;

use Exception;
use App\Factories\MailAdapterFactory;
use App\Models\EmailService;

use App\Factories\SocialAdapterFactory;
use App\Models\SocialService;

class RelayMessage
{
    /** @var MailAdapterFactory */
    protected $mailAdapter;

    /** @var SocialAdapterFactory */
    protected $socialAdapter;

    public function __construct(MailAdapterFactory $mailAdapter,SocialAdapterFactory $socialAdapter)
    {
        $this->mailAdapter = $mailAdapter;
        $this->socialAdapter = $socialAdapter;
    }

    /**
     * Dispatch the email via the email service.
     *
     * @throws Exception
     */
    public function handle_mail(string $mergedContent, MessageOptions $messageOptions, EmailService $emailService): string
    {
        return $this->mailAdapter->adapter($emailService)
            ->send(
                $messageOptions->getFromEmail(),
                $messageOptions->getFromName(),
                $messageOptions->getTo(),
                $messageOptions->getSubject(),
                $messageOptions->getTrackingOptions(),
                $mergedContent
            );
    }

    public function handle_social(string $mergedContent, MessageOptions $messageOptions, SocialService $socialService): string
    {
        return $this->socialAdapter->adapter($socialService)
            ->send(
                $messageOptions->getTo(),
                $mergedContent
            );
    }
}
