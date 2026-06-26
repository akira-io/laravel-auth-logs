# Configuration

The published `config/auth-logs.php` file controls storage, formatting, geolocation, event subscriptions, listener classes, notification templates, and retention values.

## Table Name

```php
'table_name' => 'authentication_logs',
```

The value is used by `AuthenticationLog::getTable()` and by the migration stub. Change it before running the migration, or update your schema manually if you rename the table later.

## Date Format

```php
'date_format' => 'Y-m-d H:i:s',
```

This PHP date format is used when building notification template data. It affects the text rendered in mail notifications, not the database cast.

## Geolocation API

```php
'geolocation_api' => 'http://ip-api.com/json',
```

The package appends `/{ip}` to normal HTTP-like endpoints and expects a JSON payload with `status: "success"` for non-file schemes. A failed request, invalid JSON, disabled config, or non-success status returns an empty collection.

Supported runtime shapes:

- `null` or empty string disables lookup.
- `http://...` or `https://...` calls the endpoint with a 5 second stream timeout.
- `file://...` is supported for deterministic local fixtures.
- `data://...` and `php://...` are treated as content-only streams and do not receive an appended IP.

The default config references `ip-api.com`, but custom endpoints can be used when they return compatible data.

## Database Connection

```php
'db_connection' => env('AUTH_LOGS_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
```

`AuthenticationLog::getConnectionName()` uses this value. If the value resolves to `null`, the model falls back to `database.default`.

Example:

```env
AUTH_LOGS_DB_CONNECTION=mysql
```

## Notification Channels

```php
'notification_via' => ['mail'],
```

The built-in `AuthLogsNotification` only supports `mail`. If `notifyAuthenticationLogVia()` returns `slack`, `database`, `sms`, or another channel, the built-in notification throws a `RuntimeException`.

Use custom listeners and your own Laravel notification class when you need non-mail delivery.

## Authentication Events

```php
'events' => [
    'login' => Login::class,
    'failed' => Failed::class,
    'logout' => Logout::class,
    'logout-other-devices' => OtherDeviceLogout::class,
],
```

These are the Laravel events the service provider subscribes to. Replace them only if your application dispatches compatible event classes.

## Event Listeners

```php
'listeners' => [
    'login' => LoginListener::class,
    'failed' => FailedLoginListener::class,
    'logout' => LogoutListener::class,
    'other_device_logout' => OtherDeviceLogoutListener::class,
],
```

Default behavior:

- `LoginListener` creates a successful log and may send a new device mail notification.
- `FailedLoginListener` creates a failed log only when the event contains a user instance and may send a failed login mail notification.
- `LogoutListener` calls `registerLogout()` when the event user supports that method.
- `OtherDeviceLogoutListener` is an empty hook for application customization.

## Notification Templates

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

Template classes used by built-in notifications must implement `Akira\LaravelAuthLogs\Contracts\ToMail`.

Environment toggles:

```env
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=true
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=true
```

## Log Retention Period

```php
'purge' => 365,
```

The package does not schedule or execute purges automatically. Use this value from your own scheduled command or closure. Set it to `null` only if your cleanup code explicitly handles `null`.

**Previous:** [Installation](01-installation.md) | **Next:** [Usage](03-usage.md)
