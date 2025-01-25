<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\Listeners\FailedLoginListener;
use Akira\LaravelAuthLogs\Listeners\LoginListener;
use Akira\LaravelAuthLogs\Listeners\LogoutListener;
use Akira\LaravelAuthLogs\Listeners\OtherDeviceLogoutListener;
use Akira\LaravelAuthLogs\Templates\FailedLogin;
use Akira\LaravelAuthLogs\Templates\NewDevice;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;

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
     / --------------------------------------------------------------------------
     / Notification Via
     / --------------------------------------------------------------------------
     /
     / Define the notification channels that should be used to send notifications
     / for authentication events. The default channels include 'mail'. You can
     / add other channels such as 'slack' or 'nexmo' based on your application.
     /
     */
    'notification_via' => ['mail'],

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
        'login' => Login::class,
        'failed' => Failed::class,
        'logout' => Logout::class,
        'logout-other-devices' => OtherDeviceLogout::class,
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
        'login' => LoginListener::class,
        'failed' => FailedLoginListener::class,
        'logout' => LogoutListener::class,
        'other_device_logout' => OtherDeviceLogoutListener::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | Configure templates for authentication events. You can enable or
    | disable notifications for specific actions such as detecting a new
    | device or failed login attempts. Custom templates can also be defined
    | for each notification type.
    |
    */
    'templates' => [
        'new_device' => [
            'enabled' => env('AUTH_LOGS_NEW_DEVICE_NOTIFICATION', true),
            'template' => NewDevice::class,
        ],
        'failed_login' => [
            'enabled' => env('AUTH_LOGS_FAILED_LOGIN_NOTIFICATION', true),
            'template' => FailedLogin::class,
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
