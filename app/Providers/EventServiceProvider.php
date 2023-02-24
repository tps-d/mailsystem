<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\MessageEmailDispatchEvent;
use App\Events\MessageSocialDispatchEvent;
use App\Events\SubscriberAddedEvent;
use App\Events\Webhooks\MailgunWebhookReceived;
use App\Events\Webhooks\MailjetWebhookReceived;
use App\Events\Webhooks\PostmarkWebhookReceived;
use App\Events\Webhooks\SendgridWebhookReceived;
use App\Events\Webhooks\SesWebhookReceived;
use App\Events\Webhooks\PostalWebhookReceived;
use App\Listeners\MessageEmailDispatchHandler;
use App\Listeners\MessageSocialDispatchHandler;
use App\Listeners\Webhooks\HandleMailgunWebhook;
use App\Listeners\Webhooks\HandleMailjetWebhook;
use App\Listeners\Webhooks\HandlePostmarkWebhook;
use App\Listeners\Webhooks\HandleSendgridWebhook;
use App\Listeners\Webhooks\HandleSesWebhook;
use App\Listeners\Webhooks\HandlePostalWebhook;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MailgunWebhookReceived::class => [
            HandleMailgunWebhook::class,
        ],
        MessageEmailDispatchEvent::class => [
            MessageEmailDispatchHandler::class,
        ],
        MessageSocialDispatchEvent::class => [
            MessageSocialDispatchHandler::class,
        ],
        PostmarkWebhookReceived::class => [
            HandlePostmarkWebhook::class,
        ],
        SendgridWebhookReceived::class => [
            HandleSendgridWebhook::class,
        ],
        SesWebhookReceived::class => [
            HandleSesWebhook::class
        ],
        MailjetWebhookReceived::class => [
            HandleMailjetWebhook::class
        ],
        PostalWebhookReceived::class => [
            HandlePostalWebhook::class
        ],
        SubscriberAddedEvent::class => [
            // ...
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
