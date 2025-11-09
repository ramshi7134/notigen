<?php

namespace Notigen;

use Illuminate\Support\ServiceProvider;
use Notigen\Commands\MakeNotificationCommand;
use Notigen\Commands\ManageTemplateCommand;

class NotigenServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the config file
        $this->mergeConfigFrom(
            __DIR__.'/../config/notigen.php', 'notigen'
        );

        // Register the main class as a singleton
        $this->app->singleton('notigen', function ($app) {
            return new Notigen($app);
        });

        // Register the template manager as a singleton
        $this->app->singleton(TemplateManager::class, function ($app) {
            return new TemplateManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load routes with middleware group
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'notigen');

        // Register mail components and views
        $this->loadViewComponentsAs('mail', []);
        $this->loadViewsFrom(__DIR__.'/../resources/views/mail', 'mail');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register publishable resources
        if ($this->app->runningInConsole()) {
            // Publish config and views
            $this->publishes([
                __DIR__.'/../config/notigen.php' => config_path('notigen.php'),
                __DIR__.'/../resources/views' => resource_path('views/vendor/notigen'),
                __DIR__.'/../resources/views/mail' => resource_path('views/vendor/mail'),
            ], 'notigen-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'notigen-migrations');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/notigen'),
            ], 'notigen-views');

            // Publish assets
            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/notigen'),
            ], 'notigen-assets');
        }

        // Register blade directives
        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives()
    {
        // Add custom blade directives if needed in the future
        // Example: @notigen('template-name', ['var' => 'value'])

        // Register the commands if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            
            $this->commands([
                MakeNotificationCommand::class,
                ManageTemplateCommand::class,
            ]);
        }
    }
}
