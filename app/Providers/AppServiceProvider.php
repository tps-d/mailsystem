<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

use RuntimeException;

use Illuminate\Foundation\Application;

use App\Services\Helper;
use App\Services\MailSystem;
use App\Services\ResolverService;
//use App\Facades\MailSystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->register(EventServiceProvider::class);
        $this->app->register(FormServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ConsoleServiceProvider::class);

        $this->app->bind('mailsystem', static function (Application $app) {
            $mailSystem = $app->make(MailSystem::class);
            $mailSystem->setCurrentWorkspaceIdResolver(
                static function () {

                    $user = auth()->user();
                    $request = request();
                    $workspaceId = null;

                    if ($user && $user->currentWorkspaceId()) {
                        $workspaceId = $user->currentWorkspaceId();
                    } else if ($request && (($apiToken = $request->bearerToken()) || ($apiToken = $request->get('api_token')))) {
                        $workspaceId = ApiToken::resolveWorkspaceId($apiToken);
                    }
                    
                    if (! $workspaceId) {
                        throw new RuntimeException("Current Workspace ID Resolver must not return a null value.");
                    }

                    return $workspaceId;
                }
            );

            return $mailSystem;
        });


        $this->app->singleton('mailsystem.helper', function () {
            return new Helper();
        });

        $this->app->singleton('mailsystem.resolver', function () {
            return new ResolverService();
        });

    }

    public function boot(): void
    {

    }
}
