<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Notification Channels
    |--------------------------------------------------------------------------
    |
    | This option defines the default channels that should be used when creating
    | new notifications. You can modify this list to include your preferred
    | notification channels.
    |
    */
    'default_channels' => [
        'mail',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Templates Path
    |--------------------------------------------------------------------------
    |
    | This option defines the path where your custom notification templates
    | are stored. You can publish the default templates and modify them
    | according to your needs.
    |
    */
    'templates_path' => resource_path('views/vendor/notigen/templates'),

    /*
    |--------------------------------------------------------------------------
    | Queue Notifications
    |--------------------------------------------------------------------------
    |
    | This option determines whether notifications should be queued by default.
    | You can override this setting for individual notifications by implementing
    | the ShouldQueue interface.
    |
    */
    'queue_notifications' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Queue
    |--------------------------------------------------------------------------
    |
    | This option defines the default queue that should be used for sending
    | notifications. You can modify this to use a different queue for your
    | notification processing.
    |
    */
    'default_queue' => 'default',
];
