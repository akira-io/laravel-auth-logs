# Laravel Authentication Logs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kidiatoliny/laravel-auth-logs.svg?style=flat-square)](https://packagist.org/packages/kidiatoliny/laravel-auth-logs)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/kidiatoliny/laravel-auth-logs/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kidiatoliny/laravel-auth-logs/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/kidiatoliny/laravel-auth-logs/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/kidiatoliny/laravel-auth-logs/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kidiatoliny/laravel-auth-logs.svg?style=flat-square)](https://packagist.org/packages/kidiatoliny/laravel-auth-logs)

This package logs all authentication events in your Laravel application. It logs the following events:

- Login
- Logout
- Failed login

## Features
 - Logs all authentication events
 - Notification on failed login
 - Notification on new device login

## Requirements

- PHP 8.3 or higher
- Laravel 11.0 or higher

## Installation
You can install the package via composer:

```bash
composer require akira/laravel-auth-logs
```

and then run the install command:

```bash
php artisan auth-logs:install
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-auth-logs-views"
```

## Usage

To use the package all you need to do is add the `AuthLogs` trait to your `User` model.

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
