# Installation

Follow the steps below to integrate Laravel Auth Logs into your application.

## Prerequisites
- Laravel 12 or newer
- PHP 8.4 or newer
- Composer

Check your PHP version:

```bash
php -v
```

Ensure your app can send notifications (configure queues if using queued delivery) and run database migrations.

## Install via Composer
```bash
composer require akira/laravel-auth-logs
```

## Publish Config and Migration, Then Migrate
```bash
php artisan auth-logs:install
php artisan migrate
```

This publishes `config/auth-logs.php` and the migration stub. Adjust any settings first if needed, then run migrations.

Tip: If you plan to send notifications, ensure your queues are configured; notifications implement `ShouldQueue`.

## Configuration
The configuration file is published to `config/auth-logs.php`. Adjust it to fit your needs.

```php
<?php

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
    'table_name' => 'authentication_logs',
    'date_format' => 'Y-m-d H:i:s',
    'geolocation_api' => 'http://ip-api.com/json',
    'db_connection' => env('AUTH_LOGS_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
    'notification_via' => ['mail'],
    'events' => [
        'login' => Login::class,
        'failed' => Failed::class,
        'logout' => Logout::class,
        'logout-other-devices' => OtherDeviceLogout::class,
    ],
    'listeners' => [
        'login' => LoginListener::class,
        'failed' => FailedLoginListener::class,
        'logout' => LogoutListener::class,
        'other_device_logout' => OtherDeviceLogoutListener::class,
    ],
    'templates' => [
        'new_device' => [
            'notification' => env('AUTH_LOGS_NEW_DEVICE_NOTIFICATION', true),
            'template' => NewDevice::class,
        ],
        'failed_login' => [
            'notification' => env('AUTH_LOGS_FAILED_LOGIN_NOTIFICATION', true),
            'template' => FailedLogin::class,
        ],
    ],
    'purge' => 365,
];
```

Options overview:
- table_name: Database table for logs
- date_format: Date format used in notifications
- geolocation_api: Endpoint for IP geolocation (appends `/{ip}`)
- db_connection: Connection for the model
- notification_via: Default channels to send notifications
- events: Auth events to subscribe
- listeners: Listener classes per event
- templates: Notification templates and toggles
- purge: Retention value in days (implement your own purge schedule)
