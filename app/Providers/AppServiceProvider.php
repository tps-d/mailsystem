<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

use RuntimeException;

use Illuminate\Foundation\Application;
use App\Services\Helper;

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

        $this->app->singleton('mailsystem.helper', function () {
            return new Helper();
        });
    }

    public function boot(): void
    {

    }
}
