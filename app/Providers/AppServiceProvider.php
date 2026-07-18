<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        SupportAutoInjectedAssets::$forceAssetInjection = true;
    }
}
