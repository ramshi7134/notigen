<?php

namespace Notigen\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void send(string $templateName, mixed $notifiable, array $data, array|null $channels = null)
 * @method static \Notigen\Models\NotificationTemplate|null findTemplate(string $name)
 * 
 * @see \Notigen\Notigen
 */
class Notigen extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'notigen';
    }
}
