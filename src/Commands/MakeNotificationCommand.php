<?php

namespace Notigen\Commands;

use Illuminate\Console\Command;
use Notigen\Facades\Notigen;

class MakeNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:notification-custom {name : The name of the notification class}
                          {--channels=* : The notification channels to include}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom notification class with specified channels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $channels = $this->option('channels') ?: config('notigen.default_channels', ['mail']);

        if (Notigen::createNotification($name, $channels)) {
            $this->info('Notification created successfully: ' . $name);
            $this->info('Channels included: ' . implode(', ', $channels));
        } else {
            $this->error('Failed to create notification.');
        }
    }
}
