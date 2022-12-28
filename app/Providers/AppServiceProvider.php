<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use RuntimeException;

use Illuminate\Foundation\Application;
use App\Interfaces\QuotaServiceInterface;
use App\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use App\Repositories\Campaigns\MySqlCampaignTenantRepository;
use App\Repositories\Messages\MessageTenantRepositoryInterface;
use App\Repositories\Messages\MySqlMessageTenantRepository;
use App\Repositories\Subscribers\MySqlSubscriberTenantRepository;
use App\Repositories\Subscribers\SubscriberTenantRepositoryInterface;

use App\Services\QuotaService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Campaign repository.
        $this->app->bind(CampaignTenantRepositoryInterface::class, function (Application $app) {
            return $app->make(MySqlCampaignTenantRepository::class);
        });

        // Message repository.
        $this->app->bind(MessageTenantRepositoryInterface::class, function (Application $app) {
            return $app->make(MySqlMessageTenantRepository::class);
        });

        // Subscriber repository.
        $this->app->bind(SubscriberTenantRepositoryInterface::class, function (Application $app) {
            return $app->make(MySqlSubscriberTenantRepository::class);
        });

        $this->app->bind(QuotaServiceInterface::class, QuotaService::class);

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
