# Operations

This document covers runtime operations for applications using Laravel Auth Logs.

## Queue Workers

Built-in notifications implement `ShouldQueue`.

Run workers in environments where authentication notification mail should be sent:

```bash
php artisan queue:work
```

Monitor failed jobs:

```bash
php artisan queue:failed
```

If workers are stopped, authentication logs are still written, but notifications can remain queued.

## Migrations

Run the install command and migrations during deployment or setup:

```bash
php artisan auth-logs:install
php artisan migrate
```

If you change `auth-logs.table_name`, make the change before running the migration or provide your own migration to rename the table.

## Configuration Cache

After changing `config/auth-logs.php` in a cached environment:

```bash
php artisan config:clear
php artisan config:cache
```

## Retention and Purge

The package does not schedule cleanup. Add a scheduled task in the host app:

```php
use Akira\LaravelAuthLogs\AuthenticationLog;

$schedule->call(function (): void {
    $days = (int) config('auth-logs.purge', 365);

    AuthenticationLog::query()
        ->where('login_at', '<', now()->subDays($days))
        ->delete();
})->dailyAt('03:00');
```

For high-volume applications, archive before deleting or process records in chunks.

## Observability

Recommended application-level monitoring:

- queue depth and failed jobs for auth log notifications
- number of failed login logs over time
- sudden changes in new device notification volume
- geolocation lookup failures if using external lookup
- database table growth
- purge job success and deleted row count

The package does not ship metrics or dashboards.

## Performance

Log writes happen during auth event handling. Notification delivery is queued, but notification rendering can still perform geolocation lookup when the queued job runs.

For high-traffic applications:

- keep queue workers healthy
- disable external geolocation if latency or privacy is a concern
- add indexes that match your reporting queries
- avoid synchronous exports over large date ranges
- purge or archive old rows

## Validation

For package development, run:

```bash
composer test
```

This validates formatting, refactor safety, static analysis, type coverage, and test coverage.

Coverage requires Xdebug or another coverage driver. In this workspace, Herd PHP with coverage mode is used:

```bash
PATH="/Users/kid/Library/Application Support/Herd/bin:$PATH" XDEBUG_MODE=coverage composer test
```

## Release Notes

Use [CHANGELOG.md](../CHANGELOG.md) to review behavior changes before upgrading. Pay particular attention to:

- listener behavior
- notification contracts
- Laravel version support
- database connection behavior
- test/runtime requirements

**Previous:** [Security](11-security.md) | **Next:** [FAQ](13-faq.md)
