<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Logs Table Name
    |--------------------------------------------------------------------------
    |
    | This value determines the name of the database table where authentication
    | logs will be stored. You can customize this value to fit your preferred
    | naming convention.
    |
    */
    'table_name' => 'authentication_logs',

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify the database connection that should be used for
    | storing authentication logs. If set to 'null', the default connection
    | from the database configuration will be used.
    |
    */
    'db_connection' => 'null',

    /*
    |--------------------------------------------------------------------------
    | Events to Listen To
    |--------------------------------------------------------------------------
    |
    | Define the authentication-related events that should be logged. Add
    | the class names of the events you want to monitor. This allows for
    | flexibility in tailoring the logs to your application's needs.
    |
    */
    'events' => [
        // Example: \Illuminate\Auth\Events\Login::class,
        // Add events here
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    |
    | Specify custom listeners for the defined events. These listeners can
    | handle additional logic when an event occurs. Use this array to
    | register the listeners for the authentication events.
    |
    */
    'listeners' => [
        // Example: \App\Listeners\LogAuthenticationEvent::class,
        // Add listeners here
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notifications for authentication events. You can enable or
    | disable notifications for specific actions such as detecting a new
    | device or failed login attempts. Custom templates can also be defined
    | for each notification type.
    |
    */
    'notifications' => [
        'new_device' => [
            'enabled' => env('AUTH_LOGS_NEW_DEVICE_NOTIFICATION', true),
            'location' => true, // Include location details in the notification
            'template' => 'Akira\LaravelAuthLogs\Notifications\NewDevice', // Notification class
        ],
        'failed_login' => [
            'enabled' => env('AUTH_LOGS_FAILED_LOGIN_NOTIFICATION', true),
            'location' => true, // Include location details in the notification
            'template' => 'Akira\LaravelAuthLogs\Notifications\NewDevice', // Notification class
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Retention Period
    |--------------------------------------------------------------------------
    |
    | This value determines the number of days to retain authentication logs
    | in the database. Logs older than this period will be automatically
    | purged to ensure optimal database performance.
    |
    */
    'purge' => 365,

];
