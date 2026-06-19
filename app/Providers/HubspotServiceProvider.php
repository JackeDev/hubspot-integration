<?php

namespace App\Providers;

use App\Services\Hubspot\Contracts\HubspotServiceInterface;
use App\Services\Hubspot\HubspotServices;
use Illuminate\Support\ServiceProvider;
use App\Services\Hubspot\HubSpotAuth;

class HubspotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HubspotServiceInterface::class, HubspotServices::class);
    }

    public function boot(): void
    {
        //
    }
}
