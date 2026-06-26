<div align="center">
<h1>Laravel Authentication Logs</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-auth-logs.svg)](https://packagist.org/packages/akira/laravel-auth-logs)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-auth-logs.svg)](https://packagist.org/packages/akira/laravel-auth-logs)
[![PHPStan Level](https://img.shields.io/badge/phpstan-level%209-brightgreen.svg)](https://phpstan.org)
[![License](https://img.shields.io/packagist/l/akira/laravel-auth-logs.svg)](https://github.com/akira-io/laravel-auth-logs/blob/main/LICENSE)
![img.png](img.png)

</div>

Laravel Authentication Logs records authentication activity for Laravel applications. It listens to Laravel authentication events, stores request context in a polymorphic log table, and sends mail notifications for security-relevant events.

## Features

- Records successful login attempts.
- Records failed login attempts when Laravel provides a user instance.
- Marks the latest authentication log as logged out on user logout.
- Detects new devices by successful login history for the same IP address and user agent.
- Sends queued mail notifications for failed login and new device login events.
- Resolves optional geolocation context for notification messages.
- Supports configurable table name, database connection, events, listeners, templates, notification toggles, and retention value.

The package also subscribes to Laravel's `OtherDeviceLogout` event as a customization hook. The default listener does not write a log entry.

## Requirements

- PHP 8.4 or higher
- Laravel 12.0 or 13.0
- An authenticatable model that uses Laravel notifications

The test workflow validates both Laravel 12 and Laravel 13 dependency sets.

## Installation

Install the package with Composer:

```bash
composer require akira/laravel-auth-logs
```

Publish the configuration and migration:

```bash
php artisan auth-logs:install
php artisan migrate
```

Add the `AuthLogs` trait to your authenticatable model:

```php
use Akira\LaravelAuthLogs\Concerns\AuthLogs;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, AuthLogs;
}
```

## Documentation

Full documentation is available under [docs/](docs/README.md):

- [Installation](docs/01-installation.md)
- [Configuration](docs/02-configuration.md)
- [Usage](docs/03-usage.md)
- [Notifications](docs/04-notifications.md)
- [Architecture](docs/05-architecture.md)
- [Data Flow](docs/06-data-flow.md)
- [Security](docs/07-security.md)
- [Advanced Usage](docs/08-advanced-usage.md)
- [API Reference](docs/09-api-reference.md)
- [Operations](docs/10-operations.md)
- [Testing](docs/11-testing.md)
- [Troubleshooting](docs/12-troubleshooting.md)
- [FAQ](docs/13-faq.md)
- [Roadmap](docs/14-roadmap.md)

## Testing

```bash
composer test
```

The full gate runs Pint, Rector dry-run, PHPStan, Pest type coverage, and Pest coverage.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/akira-io/laravel-auth-logs/security/policy) on how to report security vulnerabilities.

## Credits

- [kid](https://github.com/akira-io)
- [All Contributors](https://github.com/akira-io/laravel-auth-logs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
