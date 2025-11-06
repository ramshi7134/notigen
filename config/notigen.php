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
    | Templates Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the configuration for notification templates.
    | You can define templates with their unique identifiers and variables.
    |
    */
    'templates' => [
        // Example template configuration:
        // 'welcome_user' => [
        //     'name' => 'Welcome User',
        //     'description' => 'Template for welcoming new users',
        //     'channels' => ['mail', 'database'],
        //     'view' => 'notifications.welcome',
        //     'variables' => [
        //         'user_name' => 'User full name',
        //         'app_name' => 'Application name',
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can specify the route prefix and middleware for the notification
    | template manager interface. Change these values according to your needs.
    |
    */
    'route_prefix' => env('NOTIGEN_ROUTE_PREFIX', 'notigen'),

    'middleware' => [
        'web',
        'auth'
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
