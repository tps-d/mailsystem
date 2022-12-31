<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

use RuntimeException;

use Illuminate\Foundation\Application;


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

        $this->app->singleton('sendportal.resolver', function () {
            return new ResolverService();
        });
    }

    public function boot(): void
    {

    }
}
