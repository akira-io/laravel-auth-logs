<div align="center">
<h1>Laravel Authentication Logs</h1> 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-auth-logs.svg)](https://packagist.org/packages/akira/laravel-auth-logs)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-auth-logs.svg)](https://packagist.org/packages/akira/laravel-auth-logs)
[![PHPStan Level](https://img.shields.io/badge/phpstan-level%209-brightgreen.svg)](https://phpstan.org)
[![License](https://img.shields.io/packagist/l/akira/laravel-auth-logs.svg)](https://github.com/akira-io/laravel-auth-logs/blob/main/LICENSE)
![img.png](img.png)

</div>
This package logs all authentication events in your Laravel application. It logs the following events:

- Login
- Logout
- Failed login

## Features

- Logs all authentication events
- Notification on failed login
- Notification on new device login

## Requirements

- PHP 8.4 or higher
- Laravel 12.0 or higher

## Installation

You can install the package via composer:

```bash
composer require akira/laravel-auth-logs
```

and then run the install command:

```bash
php artisan auth-logs:install
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-auth-logs-views"
```

## Usage

To use the package all you need to do is add the `AuthLogs` trait to your `User` model.

## Documentation

Full documentation is available in this repository under `docs/`:

- Quick start: docs/README.md
- Installation: docs/installation.md
- Usage: docs/usage.md

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [kid](https://github.com/akira-io)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
