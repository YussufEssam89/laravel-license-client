<?php

namespace JoTech\License;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use JoTech\License\Middleware\CheckLicense;

class LicenseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/license.php', 'license'
        );

        $this->app->singleton(LicenseManager::class, function ($app) {
            return new LicenseManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/license.php' => config_path('license.php'),
        ], 'license-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/license'),
        ], 'license-views');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'license');

        // Register middleware globally (for HTTP kernel-based apps)
        if ($this->app->bound(Kernel::class)) {
            $kernel = $this->app->make(Kernel::class);

            if (method_exists($kernel, 'pushMiddleware')) {
                $kernel->pushMiddleware(CheckLicense::class);
            }
        }
    }
}
