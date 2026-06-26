# Install Guide

This is the compact installation path. See [Installation](01-installation.md) for the full guide.

## Requirements

- PHP 8.4 or newer
- Laravel 12 or 13
- Composer
- A notifiable authenticatable model if notifications are enabled

## Install

```bash
composer require akira/laravel-auth-logs
php artisan auth-logs:install
php artisan migrate
```

## Prepare Your Model

```php
use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, AuthLogs;
}
```

## Next Steps

- Review [Configuration](02-configuration.md).
- Start a queue worker if using built-in notifications.
- Read [Usage](03-usage.md) for event behavior and helper methods.
