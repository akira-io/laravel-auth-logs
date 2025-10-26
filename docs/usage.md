# Usage

This page aggregates all usage guidance: overview, model setup, events, notifications, querying, schema, cookbook, and troubleshooting.

## Overview
Once installed and configured, the package listens for Laravel auth events and records entries in the authentication logs table.

What gets logged:
- Successful logins (with timestamp, IP, user agent)
- Failed logins (with timestamp, IP, user agent)

Geolocation is looked up at notification time (using the configured endpoint) to render friendly location text.

Notifications:
- New device login (when IP + User-Agent has not been seen successfully for that user)
- Failed login attempt (when a user instance is available)

Both are configurable; see configuration in installation.

## Prepare Your Model
Add the trait to your authenticatable model and ensure it can receive notifications.

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Akira\LaravelAuthLogs\Concerns\AuthLogs;

class User extends Authenticatable
{
    use Notifiable, AuthLogs;
}
```

Provided methods:
- `authenticationLogs()`: Morph-many relationship ordered by `login_at`
- `latestAuthentication()`: Latest log (morph-one `latestOfMany`)
- `notifyAuthenticationLogVia()`: Notification channels (defaults to config)
- `registerLogout()`: Sets `logout_at` on the latest log and marks `cleared_by_user`
- `isNew()`: True if created under 1 minute ago

Override channels per model (optional):
```php
public function notifyAuthenticationLogVia(): array
{
    return ['mail'];
}
```

## Events and Listeners
Subscribed events (configurable in `config/auth-logs.php`):
- Login (`Illuminate\\Auth\\Events\\Login`)
- Failed (`Illuminate\\Auth\\Events\\Failed`)
- Logout (`Illuminate\\Auth\\Events\\Logout`)
- OtherDeviceLogout (`Illuminate\\Auth\\Events\\OtherDeviceLogout`)

Default behavior:
- Login: Creates a successful log and, if the device is new and the user isn’t brand new, sends a “new device” notification
- Failed: If a user instance exists, creates a failed log and sends a “failed login” notification
- Logout / OtherDeviceLogout: Hooks exist for customization; default listeners are no-op

Customize listeners in config:
```php
'listeners' => [
    'login' => \\App\\Listeners\\CustomLoginListener::class,
    'failed' => \\App\\Listeners\\CustomFailedLoginListener::class,
    'logout' => \\App\\Listeners\\CustomLogoutListener::class,
    'other_device_logout' => \\App\\Listeners\\CustomOtherDeviceLogoutListener::class,
],
```

## Notifications
Channels
```php
'notification_via' => ['mail']
```

Or override per model via `notifyAuthenticationLogVia()`.

Built-in templates implement `Akira\\LaravelAuthLogs\\Contracts\\ToMail`:
- `Akira\\LaravelAuthLogs\\Templates\\NewDevice`
- `Akira\\LaravelAuthLogs\\Templates\\FailedLogin`

Configure in `config/auth-logs.php`:
```php
'templates' => [
    'new_device' => [
        'notification' => env('AUTH_LOGS_NEW_DEVICE_NOTIFICATION', true),
        'template' => \\Akira\\LaravelAuthLogs\\Templates\\NewDevice::class,
    ],
    'failed_login' => [
        'notification' => env('AUTH_LOGS_FAILED_LOGIN_NOTIFICATION', true),
        'template' => \\Akira\\LaravelAuthLogs\\Templates\\FailedLogin::class,
    ],
],
```

Custom template example:
```php
use Akira\\LaravelAuthLogs\\Contracts\\ToMail;
use Illuminate\\Notifications\\Messages\\MailMessage;

final class MyCustomTemplate implements ToMail
{
    public function __construct(
        private string $loginAt,
        private string $ipAddress,
        private string $location,
        private string $userAgent,
    ) {}

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Security alert')
            ->line("IP: {$this->ipAddress}")
            ->line("When: {$this->loginAt}")
            ->line("UA: {$this->userAgent}")
            ->line("Location: {$this->location}");
    }
}
```

Queues: run a worker to deliver notifications
```bash
php artisan queue:work
```

## Querying
All logs for a user
```php
$logs = $user->authenticationLogs()->get();
```

Latest authentication log
```php
$latest = $user->latestAuthentication; // or ->latestAuthentication()->first()
```

Helper accessors
```php
$lastAt = $user->lastLoginAt();
$lastIp = $user->lastLoginIp();
$lastSuccessAt = $user->lastSuccessfulLoginAt();
$lastSuccessIp = $user->lastSuccessfulLoginIp();
$previousAt = $user->previousLoginAt();
$previousIp = $user->previousLoginIp();
```

Register a logout
```php
$user->registerLogout();
```

Detect known device (IP + UA)
```php
use Akira\\LaravelAuthLogs\\Actions\\Device;

$known = Device::isKnownFor($user, request()->ip(), request()->userAgent());
```

## Database Schema (reference)
Default columns in the logs table:
- `id` bigint unsigned
- `authenticatable_id` bigint unsigned
- `authenticatable_type` string (morph)
- `ip_address` string(45) nullable
- `user_agent` text nullable
- `login_at` timestamp nullable
- `login_successful` boolean default false
- `logout_at` timestamp nullable
- `cleared_by_user` boolean default false
- `location` json nullable

See `database/migrations/create_laravel_auth_logs_table.php.stub` for exact definitions.

## Cookbook
Purge old authentication logs (scheduler)
```php
use Akira\\LaravelAuthLogs\\AuthenticationLog;

// app/Console/Kernel.php
$schedule->call(function () {
    $days = (int) config('auth-logs.purge', 365);
    AuthenticationLog::query()
        ->where('login_at', '<', now()->subDays($days))
        ->delete();
})->dailyAt('03:00');
```

Mark logout on auth logout event
```php
use Illuminate\\Auth\\Events\\Logout;

final class MarkLogoutListener
{
    public function handle(Logout $event): void
    {
        $event->user?->registerLogout();
    }
}
```

Disable notifications with ENV
```dotenv
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=false
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=false
```

Change geolocation endpoint
```php
'geolocation_api' => env('AUTH_LOGS_GEO_API', 'http://ip-api.com/json'),
```

## Troubleshooting
Notifications aren’t delivered:
- Ensure your user model uses `Notifiable`
- Run a queue worker if using queued notifications: `php artisan queue:work`
- Check `notification_via` channels and mail configuration

Geolocation empty/incorrect:
- External endpoints may throttle or be unavailable
- Private or local IPs won’t resolve to a useful location

Logs not created on logout:
- Default logout listener is a no-op; call `$user->registerLogout()` or implement a custom logout listener

Helper methods differ across versions:
- Prefer using `authenticationLogs()` and `latestAuthentication()` queries

