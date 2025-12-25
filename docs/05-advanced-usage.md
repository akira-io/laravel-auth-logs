# Advanced Usage

This guide covers advanced scenarios and customization options for the authentication logging system.

## Custom Event Listeners

Replace or extend the default event listeners to modify logging behavior.

### Creating a Custom Listener

```php
<?php

namespace App\Listeners;

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Illuminate\Auth\Events\Login;

class CustomLoginListener
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Create the log entry
        $log = CreateAuthenticationLog::for($user, isSuccessFull: true);

        // Your custom logic
        if ($this->isSuspiciousLogin($user)) {
            $this->alertSecurityTeam($user, $log);
        }

        // Log to external service
        $this->logToDatadog($user, $log);
    }

    private function isSuspiciousLogin($user): bool
    {
        // Check for suspicious patterns
        return $user->authenticationLogs()
            ->where('login_at', '>=', now()->subMinutes(5))
            ->count() > 3;
    }
}
```

### Register the Custom Listener

Update the configuration:

```php
// config/auth-logs.php

'listeners' => [
    'login' => \App\Listeners\CustomLoginListener::class,
    'failed' => \Akira\LaravelAuthLogs\Listeners\FailedLoginListener::class,
    // ...
],
```

## Custom Actions

The package provides action classes that can be used independently or extended.

### CreateAuthenticationLog

Create authentication logs programmatically:

```php
use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;

$log = CreateAuthenticationLog::for($user, isSuccessFull: true);
```

This action automatically captures:
- Current timestamp
- Request IP address
- User agent
- Location data from the request

### SendNotification

Send authentication notifications manually:

```php
use Akira\LaravelAuthLogs\Actions\SendNotification;
use Akira\LaravelAuthLogs\Templates\NewDevice;

SendNotification::make(
    authenticatable: $user,
    template: NewDevice::class,
    log: $log
)->send();
```

### GetLocation

Fetch geolocation data for any IP address:

```php
use Akira\LaravelAuthLogs\Actions\GetLocation;

$location = GetLocation::make('8.8.8.8');

if ($location->isNotEmpty()) {
    echo $location['city'];
    echo $location['country'];
}
```

Returns an empty collection if the lookup fails.

### Device

Check if a device is known for a user:

```php
use Akira\LaravelAuthLogs\Actions\Device;

$knownDevice = Device::isKnownFor(
    user: $user,
    ip: '192.168.1.1',
    userAgent: 'Mozilla/5.0...'
);

if ($knownDevice) {
    // Device has been used before
}
```

## Custom Geolocation Provider

Replace the default geolocation provider with your own.

### Using MaxMind GeoIP2

```php
// Update config/auth-logs.php
'geolocation_api' => null, // Disable default API
```

Create a middleware to attach location data:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use GeoIp2\Database\Reader;

class AttachGeolocation
{
    public function handle($request, Closure $next)
    {
        try {
            $reader = new Reader(storage_path('geoip/GeoLite2-City.mmdb'));
            $record = $reader->city($request->ip());

            $request->location = [
                'city' => $record->city->name,
                'country' => $record->country->name,
                'lat' => $record->location->latitude,
                'lon' => $record->location->longitude,
                'timezone' => $record->location->timeZone,
            ];
        } catch (\Exception $e) {
            $request->location = [];
        }

        return $next($request);
    }
}
```

Register the middleware in `app/Http/Kernel.php`.

## Multiple Authenticatable Models

The package supports logging for any authenticatable model through polymorphic relationships.

### Setup for Admin Model

```php
<?php

namespace App\Models;

use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use AuthLogs;

    protected $guard = 'admin';
}
```

### Query Logs by Model Type

```php
use Akira\LaravelAuthLogs\AuthenticationLog;

// All user logins
$userLogs = AuthenticationLog::where('authenticatable_type', User::class)->get();

// All admin logins
$adminLogs = AuthenticationLog::where('authenticatable_type', Admin::class)->get();
```

## Rate Limiting Based on Failed Attempts

Implement rate limiting using authentication logs:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\RateLimiter;

class ThrottleFailedLogins
{
    public function handle($request, Closure $next)
    {
        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Too many attempts. Try again in {$seconds} seconds.");
        }

        return $next($request);
    }
}
```

Then in your login controller:

```php
use Illuminate\Support\Facades\RateLimiter;

protected function sendFailedLoginResponse(Request $request)
{
    RateLimiter::hit('login:' . $request->ip(), 300); // 5 minutes
    
    return back()->withErrors([
        'email' => 'Invalid credentials.',
    ]);
}
```

## Purging Old Logs

Create a scheduled command to automatically purge old logs:

```php
<?php

namespace App\Console\Commands;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgeAuthLogs extends Command
{
    protected $signature = 'auth-logs:purge';
    protected $description = 'Purge old authentication logs';

    public function handle(): int
    {
        $days = config('auth-logs.purge', 365);
        
        $deleted = AuthenticationLog::where(
            'login_at',
            '<',
            Carbon::now()->subDays($days)
        )->delete();

        $this->info("Deleted {$deleted} authentication logs.");

        return self::SUCCESS;
    }
}
```

Schedule it in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('auth-logs:purge')->daily();
}
```

## Export Authentication Logs

Create an export feature:

```php
use Akira\LaravelAuthLogs\AuthenticationLog;
use Illuminate\Support\Facades\Response;

public function exportUserLogs(User $user)
{
    $logs = $user->authenticationLogs()->get()->map(function ($log) {
        return [
            'timestamp' => $log->login_at->toDateTimeString(),
            'success' => $log->login_successful ? 'Yes' : 'No',
            'ip_address' => $log->ip_address,
            'location' => $log->location['city'] ?? 'Unknown',
            'user_agent' => $log->user_agent,
        ];
    });

    $csv = \League\Csv\Writer::createFromString('');
    $csv->insertOne(['Timestamp', 'Success', 'IP Address', 'Location', 'User Agent']);
    $csv->insertAll($logs->toArray());

    return Response::make($csv->toString(), 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="auth-logs.csv"',
    ]);
}
```

## Dashboard Integration

Create a dashboard showing authentication statistics:

```php
use Akira\LaravelAuthLogs\AuthenticationLog;
use Carbon\Carbon;

public function getDashboardStats(User $user)
{
    $logs = $user->authenticationLogs();

    return [
        'total_logins' => $logs->where('login_successful', true)->count(),
        'failed_attempts' => $logs->where('login_successful', false)->count(),
        'unique_ips' => $logs->distinct('ip_address')->count(),
        'last_login' => $user->lastSuccessfulLoginAt(),
        'logins_this_week' => $logs->where('login_at', '>=', Carbon::now()->subWeek())->count(),
        'unique_devices' => $logs->distinct('user_agent')->count(),
    ];
}
```

## Extending the AuthenticationLog Model

Create your own model extending the base model:

```php
<?php

namespace App\Models;

use Akira\LaravelAuthLogs\AuthenticationLog as BaseLog;

class AuthenticationLog extends BaseLog
{
    public function isFromMobile(): bool
    {
        return str_contains($this->user_agent, 'Mobile');
    }

    public function isSuspicious(): bool
    {
        // Custom logic
        return $this->login_at->isWeekend() && 
               $this->login_at->hour < 6;
    }
}
```

**Previous:** [Notifications](04-notifications.md) | **Next:** [API Reference](06-api-reference.md)
