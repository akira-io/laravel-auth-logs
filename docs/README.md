# Laravel Auth Logs

Track, query, and act on authentication activity in your Laravel application. Automatically records successful and failed sign-ins, with optional notifications and geolocation context.

## Quick Links
- Installation: ./installation.md
- Usage: ./usage.md
- About: ./about.md

## Features
- Automatic logging for login and failed login events
- Optional notifications (new device, failed login)
- Geolocation lookup (configurable endpoint)
- Configurable table name, DB connection, and date format

## Requirements
- PHP 8.4+
- Laravel 12+

## TL;DR (Setup)
```bash
composer require akira/laravel-auth-logs
php artisan auth-logs:install
php artisan migrate
```

Then add the trait to your authenticatable model and ensure it can receive notifications:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Akira\LaravelAuthLogs\Concerns\AuthLogs;

class User extends Authenticatable
{
    use Notifiable, AuthLogs;
}
```

See ./usage.md for details.
