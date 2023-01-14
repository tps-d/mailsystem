<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;

class MailSystem
{
    /** @var Application */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @throws BindingResolutionException
     */
    public function setCurrentWorkspaceIdResolver(callable $resolver): void
    {
        $this->app->make('mailsystem.resolver')->setCurrentWorkspaceIdResolver($resolver);
    }

    /**
     * @throws BindingResolutionException
     */
    public function currentWorkspaceId(): ?int
    {
        return $this->app->make('mailsystem.resolver')->resolveCurrentWorkspaceId();
    }


}
