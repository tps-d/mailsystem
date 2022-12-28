<?php

declare(strict_types=1);

namespace App\Services\Messages;

use Exception;
use App\Factories\MailAdapterFactory;
use App\Models\EmailService;

class RelayMessage
{
    /** @var MailAdapterFactory */
    protected $mailAdapter;

    public function __construct(MailAdapterFactory $mailAdapter)
    {
        $this->mailAdapter = $mailAdapter;
    }

    /**
     * Dispatch the email via the email service.
     *
     * @throws Exception
     */
    public function handle(string $mergedContent, MessageOptions $messageOptions, EmailService $emailService): string
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
}
