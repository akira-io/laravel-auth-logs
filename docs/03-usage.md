# Usage

Once installed and configured, the package automatically tracks authentication activity. This guide covers how to interact with authentication logs in your application.

## Basic Usage

### Automatic Logging

After adding the `AuthLogs` trait to your User model, authentication events are logged automatically:

- **Login**: Successful authentication attempts
- **Failed Login**: Failed authentication attempts with incorrect credentials
- **Logout**: User-initiated logouts
- **Other Device Logout**: When a user logs out of other devices

### Accessing Authentication Logs

Get all authentication logs for a user:

```php
$user = Auth::user();
$logs = $user->authenticationLogs;

foreach ($logs as $log) {
    echo "Login at: {$log->login_at}";
    echo "IP: {$log->ip_address}";
    echo "Location: {$log->location['city']}, {$log->location['country']}";
}
```

### Latest Authentication

Get the most recent authentication log:

```php
$latestAuth = $user->latestAuthentication;

if ($latestAuth) {
    echo "Last login: {$latestAuth->login_at}";
    echo "From: {$latestAuth->ip_address}";
}
```

## Helper Methods

The `AuthLogs` trait provides convenient methods for common queries:

### Last Login Time

```php
$lastLogin = $user->lastLoginAt();
// Returns: Carbon instance or null
```

### Last Successful Login Time

```php
$lastSuccessfulLogin = $user->lastSuccessfulLoginAt();
// Returns: Carbon instance or null
```

### Last Login IP Address

```php
$lastIp = $user->lastLoginIp();
// Returns: string or null
```

### Last Successful Login IP

```php
$lastSuccessfulIp = $user->lastSuccessfulLoginIp();
// Returns: string or null
```

### Previous Login Time

Get the login time before the most recent:

```php
$previousLogin = $user->previousLoginAt();
// Returns: Carbon instance or null
```

### Previous Login IP

```php
$previousIp = $user->previousLoginIp();
// Returns: string or null
```

### Check if User is New

Determine if a user was created within the last minute:

```php
if ($user->isNew()) {
    // User just registered
}
```

### Register Logout

Manually register a logout:

```php
$user->registerLogout();
```

This updates the latest authentication log with the logout time and marks it as cleared by the user.

## Querying Logs

### Filter by Success/Failure

```php
// Successful logins only
$successfulLogins = $user->authenticationLogs()
    ->where('login_successful', true)
    ->get();

// Failed login attempts
$failedAttempts = $user->authenticationLogs()
    ->where('login_successful', false)
    ->get();
```

### Filter by Date Range

```php
use Carbon\Carbon;

$recentLogs = $user->authenticationLogs()
    ->where('login_at', '>=', Carbon::now()->subDays(7))
    ->get();
```

### Filter by IP Address

```php
$logsFromIp = $user->authenticationLogs()
    ->where('ip_address', '192.168.1.1')
    ->get();
```

### Filter by Device

```php
$logsFromDevice = $user->authenticationLogs()
    ->where('user_agent', 'like', '%Chrome%')
    ->get();
```

## Authentication Log Model

The `AuthenticationLog` model provides access to individual log records.

### Properties

```php
$log->id                      // Primary key
$log->authenticatable_id      // User ID
$log->authenticatable_type    // User model class
$log->login_at                // Carbon instance
$log->login_successful        // boolean
$log->ip_address              // string
$log->user_agent              // string
$log->location                // array
$log->logout_at               // Carbon instance or null
$log->cleared_by_user         // boolean
```

### Relationship

Access the user from a log:

```php
$user = $log->authenticatable;
```

### Location Data

The `location` field contains geolocation data:

```php
$log->location['city']       // City name
$log->location['country']    // Country name
$log->location['timezone']   // Timezone
$log->location['lat']        // Latitude
$log->location['lon']        // Longitude
```

If geolocation lookup fails, the location array will be empty.

## Notification Channels

Override the notification channels for a specific user:

```php
class User extends Authenticatable
{
    use Notifiable, AuthLogs;

    public function notifyAuthenticationLogVia(): array
    {
        return ['mail'];
    }
}
```

The built-in notification supports `mail`. Use a custom notification implementation before returning other channels.

## Disabling Automatic Notifications

To disable notifications temporarily, set these in your `.env`:

```env
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=false
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=false
```

Or disable in code by overriding the config at runtime:

```php
config(['auth-logs.templates.new_device.notification' => false]);
```

**Previous:** [Configuration](02-configuration.md) | **Next:** [Notifications](04-notifications.md)
