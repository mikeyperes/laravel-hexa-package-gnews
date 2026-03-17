<?php

namespace hexa_package_gnews\Providers;

use Illuminate\Support\ServiceProvider;
use hexa_package_gnews\Services\GNewsService;

class GNewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GNewsService::class);
    }

    public function boot(): void {}
}
