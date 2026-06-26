# Installation

## Requirements

- PHP 8.4 or higher
- Laravel 12.0 or 13.0
- Composer
- A Laravel authenticatable model, usually `App\Models\User`
- Mail and queue configuration if you want built-in notifications delivered

The package test workflow validates both Laravel 12 and Laravel 13 dependency sets.

## 1. Install the Package

```bash
composer require akira/laravel-auth-logs
```

The package is auto-discovered by Laravel through `Akira\LaravelAuthLogs\LaravelAuthLogsServiceProvider`.

## 2. Publish Config and Migration

```bash
php artisan auth-logs:install
```

The install command publishes:

- `config/auth-logs.php`
- a migration for the configured authentication logs table

If your application uses cached configuration, clear or rebuild the cache after changing `config/auth-logs.php`:

```bash
php artisan config:clear
```

## 3. Run Migrations

```bash
php artisan migrate
```

The default table name is `authentication_logs`. The migration creates:

- `id`
- `authenticatable_type` and `authenticatable_id`
- `ip_address`
- `user_agent`
- `login_at`
- `login_successful`
- `logout_at`
- `cleared_by_user`
- `location`

The table does not include `created_at` or `updated_at`; the model has `$timestamps = false`.

## 4. Add the Trait to Your Model

Add `AuthLogs` to every authenticatable model that should have authentication logs. Add Laravel's `Notifiable` trait if the model should receive built-in notifications.

```php
<?php

namespace App\Models;

use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, AuthLogs;
}
```

The trait provides the `authenticationLogs()` and `latestAuthentication()` relationships and helper methods such as `lastLoginAt()`, `lastSuccessfulLoginIp()`, and `registerLogout()`.

## 5. Configure Mail and Queues

`AuthLogsNotification` implements `ShouldQueue`. If your application uses the built-in notifications, make sure a queue worker is running in environments where queued mail should be delivered:

```bash
php artisan queue:work
```

If no queue worker is running, log records are still written, but notification jobs can remain pending.

## Verification

1. Confirm `config/auth-logs.php` exists.
2. Confirm the migration has run and the configured table exists.
3. Confirm the authenticatable model uses `AuthLogs`.
4. Confirm the model uses `Notifiable` if notifications are enabled.
5. Log in as a non-new user and check the database for a successful authentication log.
6. Trigger a failed login for an existing user and check that a failed log is created.

**Previous:** [Roadmap](00-roadmap.md) | **Next:** [Configuration](02-configuration.md)
