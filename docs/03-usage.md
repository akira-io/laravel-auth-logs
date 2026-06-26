# Usage

Once installed, Laravel Auth Logs listens to configured Laravel authentication events and writes records through the `AuthLogs` relationship on the authenticated model.

## Default Event Behavior

After adding the `AuthLogs` trait to your authenticatable model:

- `Login` creates a successful authentication log.
- `Failed` creates a failed authentication log when Laravel provides a user instance.
- `Logout` updates the latest authentication log with `logout_at` and `cleared_by_user`.
- `OtherDeviceLogout` is subscribed as a customization hook; the default listener does not write a log entry.

## Access Logs

```php
$user = auth()->user();

$logs = $user->authenticationLogs()->get();
$latest = $user->latestAuthentication;
```

`authenticationLogs()` is a morph-many relationship ordered by latest `login_at`. `latestAuthentication()` returns the latest morph-one record.

## Helper Methods

```php
$user->lastLoginAt();
$user->lastSuccessfulLoginAt();
$user->lastLoginIp();
$user->lastSuccessfulLoginIp();
$user->previousLoginAt();
$user->previousLoginIp();
$user->isNew();
$user->registerLogout();
```

Important behavior:

- `isNew()` returns true when the model was created less than one minute ago.
- `registerLogout()` updates the latest authentication log and returns the update result.
- New device notifications are skipped for new users.

## Query Logs

```php
$successfulLogins = $user->authenticationLogs()
    ->where('login_successful', true)
    ->get();

$failedAttempts = $user->authenticationLogs()
    ->where('login_successful', false)
    ->get();

$recentLogs = $user->authenticationLogs()
    ->where('login_at', '>=', now()->subDays(7))
    ->get();

$logsFromIp = $user->authenticationLogs()
    ->where('ip_address', '192.168.1.1')
    ->get();
```

Device recognition uses exact IP address and user-agent matching against prior successful logs:

```php
use Akira\LaravelAuthLogs\Actions\Device;

$known = Device::isKnownFor($user, request()->ip(), request()->userAgent());
```

## Authentication Log Model

`AuthenticationLog` stores:

- `authenticatable_type` and `authenticatable_id`
- `ip_address`
- `user_agent`
- `login_at`
- `login_successful`
- `logout_at`
- `cleared_by_user`
- `location`

The model is final and has `$timestamps = false`; the default migration does not create `created_at` or `updated_at`.

## Location Data

`location` stores an array. When stored location contains `city` or `country`, notifications use it directly. Otherwise the package attempts a geolocation lookup during notification rendering.

If lookup fails, notification text uses `Unknown`.

Example shape:

```php
[
    'city' => 'Porto',
    'country' => 'Portugal',
    'timezone' => 'Europe/Lisbon',
    'lat' => 41.1496,
    'lon' => -8.6109,
]
```

## Notification Channels

The default channel list comes from `notifyAuthenticationLogVia()`:

```php
public function notifyAuthenticationLogVia(): array
{
    return ['mail'];
}
```

The built-in notification accepts only `mail`. Use custom listeners and a custom Laravel notification when another channel is required.

## Disable Built-in Notifications

```env
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=false
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=false
```

Or at runtime:

```php
config(['auth-logs.templates.new_device.notification' => false]);
config(['auth-logs.templates.failed_login.notification' => false]);
```

Disabling notifications does not disable log creation.

**Previous:** [Configuration](02-configuration.md) | **Next:** [Notifications](04-notifications.md)
