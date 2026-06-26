# FAQ

## Does the package log every authentication event?

No. It subscribes to login, failed login, logout, and other-device logout events, but behavior differs:

- login creates a successful log
- failed login creates a failed log only when a user exists
- logout updates the latest log
- other-device logout is an empty customization hook by default

## Why was no failed login log created?

Laravel's failed event did not include a user instance. The package cannot attach a failed log to an authenticatable model without a user.

## Why was no new device notification sent?

Common reasons:

- the device is already known by exact IP and user-agent
- the user is considered new by `isNew()`
- `AUTH_LOGS_NEW_DEVICE_NOTIFICATION=false`
- the model does not use `Notifiable`
- no queue worker is running

## Can I use Slack or SMS?

Not with the built-in notification. It only supports `mail`. Replace listeners and send your own Laravel notification for other channels.

## Can I extend `AuthenticationLog`?

No. The class is final. Use query services, presenters, resources, or application-owned records around it.

## Can I extend `AuthLogsNotification`?

No. The class is final. Use custom listeners and custom notification classes.

## Does the package purge logs automatically?

No. The `purge` config value is only a retention value for your own scheduled cleanup.

## Does geolocation run during login?

The log stores `request()->location` during log creation. If location is missing, the package attempts lookup when notification location text is rendered.

## What happens when geolocation fails?

The lookup returns an empty collection. Notification text uses `Unknown`.

## Does the table have `created_at` and `updated_at`?

No. `AuthenticationLog` has `$timestamps = false`, and the default migration does not create timestamp columns.

## Which Laravel versions are supported?

Composer allows Laravel contract packages for Laravel 12 and Laravel 13:

```json
"illuminate/contracts": "^12.0 | ^13.0"
```

## What should I run before opening a PR?

Run:

```bash
composer test
```

The gate includes style, Rector dry-run, PHPStan, type coverage, and full coverage.

**Previous:** [Operations](12-operations.md)
