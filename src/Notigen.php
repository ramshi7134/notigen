<?php

namespace Notigen;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Notigen
{
    /**
     * Create a new notification class.
     *
     * @param string $name
     * @param array $channels
     * @return bool
     */
    public function createNotification(string $name, array $channels = ['mail']): bool
    {
        $notificationPath = app_path('Notifications/' . $name . '.php');
        
        // Create the notifications directory if it doesn't exist
        if (!File::isDirectory(app_path('Notifications'))) {
            File::makeDirectory(app_path('Notifications'), 0755, true);
        }

        // Generate notification content
        $stub = File::get(__DIR__.'/stubs/notification.stub');
        $content = $this->replaceStubContent($stub, $name, $channels);

        // Create the notification file
        File::put($notificationPath, $content);

        return File::exists($notificationPath);
    }

    /**
     * Replace the stub content with actual values.
     *
     * @param string $stub
     * @param string $name
     * @param array $channels
     * @return string
     */
    protected function replaceStubContent(string $stub, string $name, array $channels): string
    {
        $channelMethods = $this->generateChannelMethods($channels);
        
        return str_replace(
            ['{{namespace}}', '{{class}}', '{{channels}}', '{{channelMethods}}'],
            [
                'App\\Notifications',
                $name,
                implode("', '", $channels),
                $channelMethods
            ],
            $stub
        );
    }

    /**
     * Generate channel methods for the notification.
     *
     * @param array $channels
     * @return string
     */
    protected function generateChannelMethods(array $channels): string
    {
        $methods = '';
        
        foreach ($channels as $channel) {
            $methodName = 'to' . Str::studly($channel);
            $methods .= $this->getChannelMethodStub($methodName, $channel);
        }
        
        return $methods;
    }

    /**
     * Get the stub for a channel method.
     *
     * @param string $methodName
     * @param string $channel
     * @return string
     */
    protected function getChannelMethodStub(string $methodName, string $channel): string
    {
        return "
    /**
     * Get the {$channel} representation of the notification.
     *
     * @param mixed \$notifiable
     * @return array
     */
    public function {$methodName}(\$notifiable)
    {
        return [
            //
        ];
    }
";
    }
}
