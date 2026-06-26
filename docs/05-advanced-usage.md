# Advanced Usage

This guide covers extension points that are supported by the current package design.

## Custom Event Listeners

The service provider subscribes listeners from `config/auth-logs.php`. Replace a listener when you need application-specific behavior.

```php
'listeners' => [
    'login' => \App\Listeners\CustomLoginListener::class,
    'failed' => \Akira\LaravelAuthLogs\Listeners\FailedLoginListener::class,
    'logout' => \Akira\LaravelAuthLogs\Listeners\LogoutListener::class,
    'other_device_logout' => \App\Listeners\CustomOtherDeviceLogoutListener::class,
],
```

Example custom login listener:

```php
<?php

namespace App\Listeners;

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Actions\Device;
use App\Notifications\SecurityTeamNotification;
use Illuminate\Auth\Events\Login;

final class CustomLoginListener
{
    public function handle(Login $event): void
    {
        $known = Device::isKnownFor(
            $event->user,
            request()->ip(),
            request()->userAgent(),
        );

        $log = CreateAuthenticationLog::for($event->user, isSuccessFull: true);

        if ($known === null) {
            $event->user->notify(new SecurityTeamNotification($log));
        }
    }
}
```

## Custom Other Device Logout Behavior

`OtherDeviceLogoutListener` is intentionally empty. Use it to add behavior when a user logs out other sessions:

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\OtherDeviceLogout;

final class CustomOtherDeviceLogoutListener
{
    public function handle(OtherDeviceLogout $event): void
    {
        logger()->info('User logged out other devices.', [
            'user_id' => $event->user?->getAuthIdentifier(),
        ]);
    }
}
```

## Direct Actions

Use package actions when a custom listener needs the same low-level behavior.

### Create a Log

```php
use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;

$log = CreateAuthenticationLog::for($user, isSuccessFull: true);
```

The action reads the current request IP, user agent, and `request()->location`.

### Send a Built-in Mail Notification

```php
use Akira\LaravelAuthLogs\Actions\SendNotification;
use Akira\LaravelAuthLogs\Templates\NewDevice;

SendNotification::make(
    authenticatable: $user,
    template: NewDevice::class,
    log: $log,
)->send();
```

The template class must implement `ToMail`.

### Resolve Location

```php
use Akira\LaravelAuthLogs\Actions\GetLocation;

$location = GetLocation::make('8.8.8.8');

if ($location->isNotEmpty()) {
    $city = $location->get('city');
}
```

The action returns an empty collection when lookup is disabled, unavailable, invalid, or unsuccessful.

### Detect Known Devices

```php
use Akira\LaravelAuthLogs\Actions\Device;

$known = Device::isKnownFor(
    user: $user,
    ip: request()->ip(),
    userAgent: request()->userAgent(),
);
```

The match is exact and only considers prior successful logs.

## Custom Geolocation

You can provide location data before the package creates the log:

```php
request()->merge([
    'location' => [
        'city' => 'Porto',
        'country' => 'Portugal',
        'lat' => 41.1496,
        'lon' => -8.6109,
        'timezone' => 'Europe/Lisbon',
    ],
]);
```

You can also disable the built-in lookup and handle geolocation yourself:

```php
'geolocation_api' => null,
```

When notification rendering cannot resolve location data, the package uses `Unknown`.

## Multiple Authenticatable Models

The log table uses a morph relationship, so multiple authenticatable models can use the trait.

```php
use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class Admin extends Authenticatable
{
    use Notifiable, AuthLogs;
}
```

Query by model type:

```php
use Akira\LaravelAuthLogs\AuthenticationLog;

$adminLogs = AuthenticationLog::query()
    ->where('authenticatable_type', Admin::class)
    ->get();
```

## Purging Old Logs

The package provides a retention value but does not schedule cleanup. Add cleanup to your application:

```php
use Akira\LaravelAuthLogs\AuthenticationLog;

$schedule->call(function (): void {
    $days = (int) config('auth-logs.purge', 365);

    AuthenticationLog::query()
        ->where('login_at', '<', now()->subDays($days))
        ->delete();
})->dailyAt('03:00');
```

## Exporting Logs

```php
$rows = $user->authenticationLogs()
    ->latest('login_at')
    ->get()
    ->map(fn ($log): array => [
        'login_at' => $log->login_at?->toDateTimeString(),
        'success' => $log->login_successful,
        'ip_address' => $log->ip_address,
        'user_agent' => $log->user_agent,
        'location' => $log->location,
    ]);
```

Authentication logs contain IP addresses, user agents, timestamps, and geolocation. Treat exports as sensitive data.

## Dashboard Queries

```php
$query = $user->authenticationLogs();

$stats = [
    'successful_logins' => (clone $query)->where('login_successful', true)->count(),
    'failed_attempts' => (clone $query)->where('login_successful', false)->count(),
    'unique_ips' => (clone $query)->distinct('ip_address')->count('ip_address'),
    'last_successful_login' => $user->lastSuccessfulLoginAt(),
];
```

For high-volume tables, add application migrations for the indexes your dashboards need.

## Extending the Model

`AuthenticationLog` is final. Do not extend it. Prefer:

- custom query objects
- Eloquent scopes in application services
- presenters or resources around `AuthenticationLog`
- custom listeners that write additional application-owned records

Example presenter:

```php
final readonly class AuthenticationLogPresenter
{
    public function __construct(private AuthenticationLog $log) {}

    public function isSuspicious(): bool
    {
        return $this->log->login_successful === false;
    }
}
```

**Previous:** [Notifications](04-notifications.md) | **Next:** [API Reference](06-api-reference.md)
