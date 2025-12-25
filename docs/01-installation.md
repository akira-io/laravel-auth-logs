# Installation

## Requirements

- PHP 8.4 or higher
- Laravel 12.0 or higher

## Installation Steps

### 1. Install via Composer

```bash
composer require akira/laravel-auth-logs
```

### 2. Run the Install Command

The package includes an installation command that publishes the configuration and migration files:

```bash
php artisan auth-logs:install
```

This command will:
- Publish the configuration file to `config/auth-logs.php`
- Publish the migration file to `database/migrations/`

### 3. Run Migrations

Run the migration to create the authentication logs table:

```bash
php artisan migrate
```

The migration creates a table (default name: `authentication_logs`) with the following columns:
- `id` - Primary key
- `authenticatable_type` and `authenticatable_id` - Polymorphic relationship to your User model
- `ip_address` - IP address of the authentication attempt
- `user_agent` - Browser and device information
- `login_at` - Timestamp of the authentication attempt
- `login_successful` - Boolean indicating success or failure
- `logout_at` - Timestamp when user logged out
- `cleared_by_user` - Boolean indicating if logout was user-initiated
- `location` - JSON field storing geolocation data

### 4. Add the Trait to Your User Model

Add the `AuthLogs` trait to your authenticatable model (typically `App\Models\User`):

```php
<?php

namespace App\Models;

use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, AuthLogs;

    // ... rest of your model
}
```

## Optional: Publish Views

If you want to customize the email notification views, publish them:

```bash
php artisan vendor:publish --tag="laravel-auth-logs-views"
```

## Optional: Publish Translations

To customize notification messages:

```bash
php artisan vendor:publish --tag="laravel-auth-logs-translations"
```

## Verification

To verify the installation is complete:

1. Ensure the `authentication_logs` table exists in your database
2. Verify the `config/auth-logs.php` file is present
3. Confirm the `AuthLogs` trait is added to your User model
4. Test by logging in and checking the database for a new authentication log entry

**Previous:** [Roadmap](00-roadmap.md) | **Next:** [Configuration](02-configuration.md)
