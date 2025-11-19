<?php

namespace Lavalite\Core;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Lavalite\Core\Http\Middleware\EnsureOrganizationAccess;
use Lavalite\Core\Http\Middleware\SetOrganizationContext;
use Lavalite\Core\Services\AuthServiceClient;

class LavaliteCoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/lavalite-core.php',
            'lavalite-core'
        );

        // Register AuthServiceClient as singleton
        $this->app->singleton(AuthServiceClient::class, function ($app) {
            return new AuthServiceClient;
        });

        // Register alias for AuthServiceClient
        $this->app->alias(AuthServiceClient::class, 'lavalite.auth-client');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/lavalite-core.php' => config_path('lavalite-core.php'),
        ], 'lavalite-core-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'lavalite-core-migrations');

        // Load migrations if not published
        if (! $this->app->configurationIsCached()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Register middleware
        $this->registerMiddleware();

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add console commands here
            ]);
        }
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        if (! config('lavalite-core.auto_register_middleware', true)) {
            return;
        }

        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('organization', SetOrganizationContext::class);
        $router->aliasMiddleware('organization.access', EnsureOrganizationAccess::class);
    }
}
