# Configuration

The configuration file `config/auth-logs.php` controls all aspects of the authentication logging behavior.

## Table Name

Customize the database table name for storing authentication logs:

```php
'table_name' => 'authentication_logs',
```

You can change this to match your application's naming conventions.

## Date Format

Define the date format used in notifications:

```php
'date_format' => 'Y-m-d H:i:s',
```

This format follows PHP's `date()` function conventions.

## Geolocation API

Configure the API endpoint for fetching geolocation data:

```php
'geolocation_api' => 'http://ip-api.com/json',
```

Currently, only `ip-api.com` is supported. The package fetches location data (city, country, timezone) based on the user's IP address.

## Database Connection

Specify which database connection to use:

```php
'db_connection' => env('AUTH_LOGS_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
```

Set `AUTH_LOGS_DB_CONNECTION` in your `.env` file to use a different connection, or leave it `null` to use your default connection.

## Notification Channels

Define how authentication notifications are delivered:

```php
'notification_via' => ['mail'],
```

The built-in notification supports the `mail` channel. Use a custom notification implementation if your application needs Slack, SMS, database, or another channel.

## Authentication Events

Configure which Laravel authentication events to monitor:

```php
'events' => [
    'login' => Login::class,
    'failed' => Failed::class,
    'logout' => Logout::class,
    'logout-other-devices' => OtherDeviceLogout::class,
],
```

These events are automatically fired by Laravel during authentication. You can replace them with custom event classes if needed.

## Event Listeners

Register custom listeners for authentication events:

```php
'listeners' => [
    'login' => LoginListener::class,
    'failed' => FailedLoginListener::class,
    'logout' => LogoutListener::class,
    'other_device_logout' => OtherDeviceLogoutListener::class,
],
```

You can extend or replace these listeners with your own implementations to customize logging behavior.

## Notification Templates

Control when notifications are sent and which templates to use:

```php
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
```

### New Device Notification

Sent when a user logs in from an unrecognized device (new IP + user-agent combination). Set `AUTH_LOGS_NEW_DEVICE_NOTIFICATION=false` in `.env` to disable.

### Failed Login Notification

Sent when someone attempts to log in with incorrect credentials. Set `AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=false` in `.env` to disable.

You can create custom templates by implementing the `Akira\LaravelAuthLogs\Contracts\ToMail` contract.

## Log Retention Period

Configure how long authentication logs are retained:

```php
'purge' => 365,
```

Logs older than this number of days will be purged. Set to `null` to keep logs indefinitely.

## Environment Variables

Add these variables to your `.env` file for quick configuration:

```env
# Database connection (optional)
AUTH_LOGS_DB_CONNECTION=mysql

# Notification toggles
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=true
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=true
```

**Previous:** [Installation](01-installation.md) | **Next:** [Usage](03-usage.md)
