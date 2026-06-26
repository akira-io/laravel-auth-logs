# Usage Guide

This is the compact usage guide. See [Usage](03-usage.md), [Notifications](04-notifications.md), and [Data Flow](10-data-flow.md) for the full behavior.

## What the Package Does

- Logs successful login attempts.
- Logs failed login attempts when the failed event has a user instance.
- Marks the latest log as logged out on `Logout`.
- Subscribes to `OtherDeviceLogout` as an empty customization hook.
- Sends queued mail notifications for failed login and new device login when enabled.

## Model Setup

```php
use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, AuthLogs;
}
```

## Querying

```php
$logs = $user->authenticationLogs()->get();
$latest = $user->latestAuthentication;

$lastAt = $user->lastLoginAt();
$lastIp = $user->lastLoginIp();
$lastSuccessAt = $user->lastSuccessfulLoginAt();
$lastSuccessIp = $user->lastSuccessfulLoginIp();
$previousAt = $user->previousLoginAt();
$previousIp = $user->previousLoginIp();
```

## Logout

```php
$user->registerLogout();
```

The default `LogoutListener` calls this method automatically when the event user supports it.

## Notifications

```php
public function notifyAuthenticationLogVia(): array
{
    return ['mail'];
}
```

Only `mail` is supported by the built-in notification. Use custom listeners and your own Laravel notification for other channels.
