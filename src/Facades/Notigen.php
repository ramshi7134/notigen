<?php

namespace Notigen\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool createNotification(string $name, array $channels = ['mail'])
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
