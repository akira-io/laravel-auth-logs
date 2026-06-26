# Laravel Auth Logs Documentation

Laravel Auth Logs records authentication activity in Laravel applications. It stores login context, exposes helper methods on authenticatable models, and sends queued mail notifications for failed login and new device login events.

## Getting Started

- [Installation](01-installation.md): install the package, publish configuration and migration files, migrate the database, and prepare your authenticatable model.
- [Configuration](02-configuration.md): configure table names, database connections, geolocation, event listeners, notification channels, templates, and retention values.
- [Usage](03-usage.md): read authentication logs, understand default event behavior, query login history, and use model helper methods.
- [Notifications](04-notifications.md): customize mail templates, notification toggles, queue behavior, and channel boundaries.

## Understand the System

- [Architecture](05-architecture.md): package components, responsibilities, extension points, and runtime boundaries.
- [Data Flow](06-data-flow.md): login, failed login, logout, notification, geolocation, and retention flows.
- [Security](07-security.md): stored data, privacy boundaries, notification risks, geolocation risk, and safe usage.

## Extend and Operate

- [Advanced Usage](08-advanced-usage.md): replace listeners, use actions directly, add custom geolocation, build dashboards, and purge logs.
- [API Reference](09-api-reference.md): review public traits, models, actions, contracts, commands, and value objects.
- [Operations](10-operations.md): queue workers, purge jobs, migrations, releases, observability, and validation.
- [Testing](11-testing.md): test logging behavior, notifications, geolocation, and package integration.
- [Troubleshooting](12-troubleshooting.md): diagnose installation, migration, configuration, notification, geolocation, and performance issues.

## Appendix

- [FAQ](13-faq.md): common developer questions and short answers.
- [Roadmap](14-roadmap.md): potential future improvements that are not current behavior.

## Requirements

- PHP 8.4 or higher
- Laravel 12 or Laravel 13
- A notifiable authenticatable model, usually `App\Models\User`

Run the package test suite during development:

```bash
composer test
```
