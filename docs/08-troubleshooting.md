# Troubleshooting

Common issues and solutions when working with Laravel Auth Logs.

## Installation Issues

### Migration File Not Found

**Problem:** Running `php artisan migrate` shows no migration file.

**Solution:**
```bash
# Re-run the installation command
php artisan auth-logs:install

# Check if migration was published
ls database/migrations/*laravel_auth_logs*

# Manually publish migrations if needed
php artisan vendor:publish --tag="auth-logs-migrations"
```

### Config File Not Found

**Problem:** Configuration values are not being recognized.

**Solution:**
```bash
# Clear config cache
php artisan config:clear

# Republish config
php artisan vendor:publish --tag="auth-logs-config" --force

# Verify file exists
ls config/auth-logs.php
```

### Table Already Exists Error

**Problem:** Migration fails with "table already exists" error.

**Solution:**
```bash
# Check if table exists
php artisan db:show

# Drop the table if needed
php artisan tinker
>>> Schema::dropIfExists('authentication_logs');

# Or use a fresh migration
php artisan migrate:fresh
```

## Logging Issues

### Logs Not Being Created

**Problem:** Authentication events are not being logged.

**Checklist:**
1. Verify the `AuthLogs` trait is added to your User model:
```php
use Akira\LaravelAuthLogs\Concerns\AuthLogs;

class User extends Authenticatable
{
    use AuthLogs;
}
```

2. Check that the migration has been run:
```bash
php artisan migrate:status
```

3. Verify event listeners are registered:
```bash
php artisan event:list | grep -i login
```

4. Check database connection configuration:
```php
// config/auth-logs.php
'db_connection' => env('AUTH_LOGS_DB_CONNECTION', env('DB_CONNECTION')),
```

### Wrong Table Name

**Problem:** Logs are being written to the wrong table or table not found.

**Solution:**
```php
// config/auth-logs.php
'table_name' => 'authentication_logs', // Verify this matches your migration

// Clear cache after changing
php artisan config:clear
```

### Polymorphic Relationship Issues

**Problem:** Cannot retrieve user from authentication log.

**Solution:**
```php
// Ensure your User model namespace is correct
$log->authenticatable; // Should return User instance

// Check the authenticatable_type column contains correct class name
// Should be: App\Models\User (not User)
```

## Notification Issues

### Notifications Not Being Sent

**Problem:** Email notifications are not arriving.

**Checklist:**

1. Verify queue is running:
```bash
php artisan queue:work
```

2. Check notification configuration:
```php
// config/auth-logs.php
'templates' => [
    'new_device' => [
        'notification' => env('AUTH_LOGS_NEW_DEVICE_NOTIFICATION', true),
    ],
],
```

3. Verify `.env` settings:
```env
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=true
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=true
```

4. Check mail configuration:
```bash
php artisan tinker
>>> Mail::raw('Test', function($message) { $message->to('test@example.com')->subject('Test'); });
```

5. Inspect failed jobs:
```bash
php artisan queue:failed
```

### Notifications Sent for New Users

**Problem:** New device notifications sent immediately after registration.

**Explanation:** This is expected behavior. The package skips notifications for users created within the last minute using the `isNew()` method.

**Workaround:** If you need different behavior, override in your User model:
```php
public function isNew(): bool
{
    return $this->created_at->diffInMinutes(now()) < 5; // 5 minutes instead of 1
}
```

### Duplicate Notifications

**Problem:** Multiple notification emails are being sent for one login.

**Causes:**
1. Multiple queue workers processing the same job
2. Event listeners registered multiple times

**Solution:**
```bash
# Restart queue workers
php artisan queue:restart

# Clear cached events
php artisan event:clear
php artisan config:clear

# Check for duplicate listener registrations in your EventServiceProvider
```

## Geolocation Issues

### Location Data Not Populated

**Problem:** The `location` field is empty or null.

**Checklist:**

1. Verify the geolocation API is reachable:
```bash
curl http://ip-api.com/json/8.8.8.8
```

2. Check if your server can make outbound HTTP requests:
```php
// Test in tinker
php artisan tinker
>>> file_get_contents('http://ip-api.com/json/8.8.8.8');
```

3. For localhost testing, location data won't work with `127.0.0.1`. Use a real IP:
```php
// In tests, mock the request location
$request->location = ['city' => 'Test', 'country' => 'Test'];
```

4. Check firewall/proxy settings that might block outbound requests.

### Alternative Geolocation Provider

**Problem:** Need to use a different geolocation service.

**Solution:**
```php
// Create middleware to set custom location
namespace App\Http\Middleware;

class SetCustomLocation
{
    public function handle($request, $next)
    {
        $ip = $request->ip();
        
        // Use your preferred service
        $location = YourGeoService::lookup($ip);
        
        $request->location = [
            'city' => $location->city,
            'country' => $location->country,
            'lat' => $location->latitude,
            'lon' => $location->longitude,
            'timezone' => $location->timezone,
        ];
        
        return $next($request);
    }
}
```

## Performance Issues

### Slow Login Performance

**Problem:** Login process has become noticeably slower.

**Solutions:**

1. Ensure notifications are queued (default behavior):
```php
// Verify AuthLogsNotification implements ShouldQueue
class AuthLogsNotification extends Notification implements ShouldQueue
```

2. Add database indexes:
```php
// Create a migration
Schema::table('authentication_logs', function (Blueprint $table) {
    $table->index(['authenticatable_type', 'authenticatable_id']);
    $table->index('login_at');
    $table->index(['ip_address', 'user_agent']);
});
```

3. Disable geolocation for high-traffic applications:
```php
// config/auth-logs.php
'geolocation_api' => null, // Disable
```

### Large Database Table

**Problem:** The authentication_logs table is consuming too much disk space.

**Solutions:**

1. Schedule cleanup in your application:
```php
use Akira\LaravelAuthLogs\AuthenticationLog;

$schedule->call(function (): void {
    $days = (int) config('auth-logs.purge', 365);

    AuthenticationLog::query()
        ->where('login_at', '<', now()->subDays($days))
        ->delete();
})->daily();
```

2. Reduce retention period:
```php
// config/auth-logs.php
'purge' => 90, // Keep only 90 days instead of 365
```

3. Archive old logs:
```php
// Create custom command
AuthenticationLog::where('login_at', '<', now()->subYear())
    ->chunk(1000, function ($logs) {
        Storage::append('archived-logs.json', $logs->toJson());
        $logs->each->delete();
    });
```

## Testing Issues

### Cannot Mock Notifications

**Problem:** `Notification::fake()` not working in tests.

**Solution:**
```php
use Illuminate\Support\Facades\Notification;

public function setUp(): void
{
    parent::setUp();
    Notification::fake(); // Call before any authentication
}
```

### Database Not Refreshing

**Problem:** Tests fail due to existing data.

**Solution:**
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthLogTest extends TestCase
{
    use RefreshDatabase; // Ensures fresh database for each test
}
```

### Time-based Tests Failing

**Problem:** Tests involving `isNew()` are flaky.

**Solution:**
```php
use Illuminate\Support\Facades\Date;

public function test_new_user_detection()
{
    Date::setTestNow('2025-01-01 12:00:00');
    
    $user = User::factory()->create();
    
    $this->assertTrue($user->isNew());
    
    Date::setTestNow('2025-01-01 12:02:00'); // 2 minutes later
    
    $this->assertFalse($user->isNew());
}
```

## Common Errors

### `Call to undefined method authenticationLogs()`

**Cause:** The `AuthLogs` trait is not added to the User model.

**Solution:**
```php
use Akira\LaravelAuthLogs\Concerns\AuthLogs;

class User extends Authenticatable
{
    use Notifiable, AuthLogs; // Add here
}
```

### `Class 'NewDevice' not found`

**Cause:** Template class cannot be resolved.

**Solution:**
```php
// Verify the full class name in config
'template' => \Akira\LaravelAuthLogs\Templates\NewDevice::class,

// Clear config cache
php artisan config:clear
```

### `SQLSTATE[42S02]: Base table or view not found`

**Cause:** Migration has not been run.

**Solution:**
```bash
php artisan migrate
```

### `Undefined property: authenticatable_id`

**Cause:** Using the wrong query method or model is not loaded.

**Solution:**
```php
// Always eager load the relationship when querying
$logs = AuthenticationLog::with('authenticatable')->get();

// Or access via user
$user->authenticationLogs;
```

## Debug Mode

Enable detailed logging for troubleshooting:

```php
// In a service provider or middleware
\Log::info('Auth event triggered', [
    'user_id' => $user->id,
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);
```

Add to listeners:
```php
public function handle(Login $event): void
{
    \Log::debug('LoginListener triggered', [
        'user' => $event->user->email,
    ]);
    
    // existing code...
}
```

## Getting Help

If you're still experiencing issues:

1. Check the [GitHub Issues](https://github.com/akira-io/laravel-auth-logs/issues)
2. Enable Laravel debug mode: `APP_DEBUG=true`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify package version: `composer show akira/laravel-auth-logs`
5. Clear all caches: `php artisan optimize:clear`

**Previous:** [Testing](07-testing.md)
