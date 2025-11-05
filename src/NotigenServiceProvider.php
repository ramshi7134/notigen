<?php

namespace Notigen;

use Illuminate\Support\ServiceProvider;
use Notigen\Commands\MakeNotificationCommand;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/notigen.php' => config_path('notigen.php'),
        ], 'notigen-config');

        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeNotificationCommand::class,
            ]);
        }
    }
}
