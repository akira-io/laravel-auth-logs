# Troubleshooting

Use this guide to diagnose common installation, logging, notification, geolocation, and test issues.

## Migration File Not Found

Run the install command again:

```bash
php artisan auth-logs:install
```

Then check that the migration exists and run migrations:

```bash
php artisan migrate:status
php artisan migrate
```

## Config Values Are Not Applied

Clear cached configuration:

```bash
php artisan config:clear
```

Confirm `config/auth-logs.php` exists and that the app is reading the expected environment:

```bash
php artisan tinker
>>> config('auth-logs.table_name')
```

## Logs Are Not Created

Check:

1. The authenticatable model uses `Akira\LaravelAuthLogs\Concerns\AuthLogs`.
2. The configured migration has run.
3. The service provider is registered through package auto-discovery.
4. The event is configured in `auth-logs.events`.
5. The event listener is configured in `auth-logs.listeners`.

List registered events:

```bash
php artisan event:list
```

## Failed Logins Are Not Logged

`FailedLoginListener` only logs failed attempts when the Laravel `Failed` event contains a user instance. If the credentials do not resolve to a user, the listener returns without writing a log.

## Logout Is Not Marked

`LogoutListener` calls `registerLogout()` only when the event user has that method. Confirm the model uses `AuthLogs`.

`OtherDeviceLogoutListener` is empty by default. Add a custom listener if your application needs behavior for "log out other devices".

## Notifications Are Not Delivered

Check:

1. The model uses `Illuminate\Notifications\Notifiable`.
2. The relevant notification toggle is true.
3. `notifyAuthenticationLogVia()` returns `['mail']`.
4. Mail configuration is valid.
5. A queue worker is running.
6. Failed jobs do not contain the notification.

Commands:

```bash
php artisan queue:work
php artisan queue:failed
```

## Unsupported Notification Channel Error

The built-in notification supports only `mail`. Returning another channel causes:

```text
Laravel Auth Logs only supports the mail notification channel by default.
```

Use custom listeners and your own notification class for Slack, SMS, database, push, or other channels.

## Template Contract Error

Built-in templates must implement `Akira\LaravelAuthLogs\Contracts\ToMail`.

Check the configured template:

```php
'templates' => [
    'new_device' => [
        'template' => \App\Notifications\AuthLogs\SecurityNewDevice::class,
    ],
],
```

The class must accept `loginAt`, `ipAddress`, `location`, and `userAgent` constructor strings and implement `toMail()`.

## Geolocation Is Empty

Empty geolocation is expected when:

- `auth-logs.geolocation_api` is `null` or empty
- the endpoint cannot be reached
- the endpoint returns invalid JSON
- a non-file endpoint returns a status other than `success`
- the IP is local, private, proxied, or blocked by the provider

For notifications, empty geolocation renders as `Unknown`.

## Login Becomes Slow

Log creation is database work on the request path. Built-in notification mail is queued, but location resolution can happen while the queued notification is rendered.

For high-traffic apps:

- run queue workers separately from web requests
- consider disabling built-in geolocation
- provide location data from your own middleware
- add indexes for your query patterns
- purge or archive old logs

## Table Is Too Large

The package does not purge records automatically. Schedule cleanup in the host application:

```php
use Akira\LaravelAuthLogs\AuthenticationLog;

$schedule->call(function (): void {
    $days = (int) config('auth-logs.purge', 365);

    AuthenticationLog::query()
        ->where('login_at', '<', now()->subDays($days))
        ->delete();
})->daily();
```

## Composer Test Fails Without Coverage Driver

`composer test` runs `pest --parallel --coverage --exactly=100 --compact`. Install or enable a coverage driver such as Xdebug or PCOV.

With Herd PHP, a typical local command is:

```bash
PATH="/Users/kid/Library/Application Support/Herd/bin:$PATH" XDEBUG_MODE=coverage composer test
```

## PHPStan Runs Out of Memory

Increase PHP memory for the runtime running Composer and PHPStan. Herd PHP is configured with a higher memory limit in this workspace. Other runtimes may need a `memory_limit` update or a PHPStan `--memory-limit` option.

## Random Parallel Test Failure

The suite runs in parallel for coverage. If a failure appears order-dependent:

1. rerun with the random seed printed by Pest
2. check for mutated config that is not reset in the test
3. check service-provider bootstrapping state
4. rerun the full `composer test` gate after isolating the shared state

## Getting Help

Before opening an issue:

1. Confirm your Laravel, PHP, and package versions.
2. Include the relevant `auth-logs.php` config.
3. Include the event/listener you expected to run.
4. Include queue and mail error output for notification issues.
5. Include a minimal reproduction when possible.

**Previous:** [Testing](07-testing.md)
