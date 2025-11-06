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
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'notigen');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/notigen.php' => config_path('notigen.php'),
        ], 'notigen-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'notigen-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/notigen'),
        ], 'notigen-views');

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
