<?php

namespace hexa_package_gnews\Providers;

use Illuminate\Support\ServiceProvider;
use hexa_package_gnews\Services\GNewsService;
use hexa_core\Services\PackageRegistryService;

/**
 * GNewsServiceProvider — registers GNews package services, routes, views.
 */
class GNewsServiceProvider extends ServiceProvider
{
    /**
     * Register services into the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/gnews.php', 'gnews');
        $this->app->singleton(GNewsService::class);
    }

    /**
     * Bootstrap package resources.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/gnews.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'gnews');

        // Sidebar links — registered via PackageRegistryService with auto permission checks
        if (!config('hexa.app_controls_sidebar', false)) {
            $registry = app(PackageRegistryService::class);
            // HWS-SIDEBAR-MENU-3L-BEGIN
            $registry->registerDomainGroup('Discovery', 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 20);
            $registry->registerSectionGroup('Sandbox', 'Discovery', '', 20);
            // HWS-SIDEBAR-MENU-3L-END

            $registry->registerSidebarLink('gnews.index', 'GNews', 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z', 'Sandbox', 'gnews', 81);
            $registry->registerPackage('gnews', 'hexawebsystems/laravel-hexa-package-gnews', [
                'title' => 'GNews',
                'description' => 'GNews source configuration for news discovery and article sourcing.',
                'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
                'color' => 'sky',
                'settingsRoute' => 'settings.gnews',
            ]);
        }

        // Settings card on /settings page
        $this->registerSettingsCard();
    }

    /**
     * Register settings card on the core settings page.
     *
     * @return void
     */
    private function registerSettingsCard(): void
    {
        view()->composer('settings.index', function ($view) {
            $view->getFactory()->startPush('settings-cards', view('gnews::partials.settings-card')->render());
        });
    }
}
