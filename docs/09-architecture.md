# Architecture

Laravel Auth Logs is a Laravel package built around event listeners, small action classes, a polymorphic Eloquent model, and queued mail notifications.

## System Boundary

The package owns:

- service-provider registration
- config, migration, translations, views, and install command registration
- authentication event subscriptions
- authentication log creation and helper relationships
- built-in mail notification templates
- geolocation lookup helper used while rendering notifications

The host application owns:

- authentication guards and flows
- queue workers
- mail transport configuration
- log retention scheduling
- authorization around any UI or API that exposes logs
- non-mail notification channels
- any custom listener behavior

## Main Components

### Service Provider

`LaravelAuthLogsServiceProvider` registers package resources through Spatie Package Tools and subscribes configured authentication events to configured listeners.

It also guards against a non-array `auth-logs` config value before package config merging.

### Configuration

`config/auth-logs.php` is the runtime contract for:

- table name
- notification date format
- geolocation endpoint
- database connection
- notification channels
- event classes
- listener classes
- notification templates
- retention value

### Model and Trait

`AuthenticationLog` is the final Eloquent model for stored records. The model:

- is polymorphic through `authenticatable()`
- has `$timestamps = false`
- reads its connection from `auth-logs.db_connection`
- reads its table from `auth-logs.table_name`
- casts date, boolean, and location fields

`AuthLogs` is added to authenticatable models. It provides relationships and helper methods for querying recent and successful login activity.

### Listeners

The default listeners are intentionally small:

- `LoginListener` logs successful login and optionally sends a new device notification.
- `FailedLoginListener` logs failed login for existing users and optionally sends a failed login notification.
- `LogoutListener` marks the latest log as logged out.
- `OtherDeviceLogoutListener` is an empty customization hook.

### Actions

Actions hold reusable behavior:

- `CreateAuthenticationLog` writes a record from the current request context.
- `Device` checks prior successful login records for an exact IP and user-agent match.
- `SendNotification` validates template configuration and dispatches the notification.
- `GetLocation` fetches geolocation data from configured stream endpoints.

### Notifications and Templates

`AuthLogsNotification` is a final queued Laravel notification. It supports only the `mail` channel by default.

Templates must implement `ToMail`, which extends `Template`. The package provides:

- `NewDevice`
- `FailedLogin`

## Extension Points

Supported extension points:

- replace listeners in config
- replace mail templates with `ToMail` implementations
- override `notifyAuthenticationLogVia()` while still returning `mail` for the built-in notification
- provide request location data before log creation
- schedule your own purge job
- build application-owned APIs, dashboards, exports, or alerting around `AuthenticationLog`

Unsupported extension assumptions:

- Do not extend `AuthenticationLog`; it is final.
- Do not extend `AuthLogsNotification`; it is final.
- Do not return non-mail channels to the built-in notification.

## Dependency Direction

Host applications depend on the package. Package internals do not depend on application models beyond Laravel's `Authenticatable` contract and the `AuthLogs` relationship methods being present at runtime.

## Data Storage

The default migration stores authentication context in a single table:

- morph owner columns
- IP address
- user-agent
- login timestamp
- success flag
- logout timestamp
- cleared-by-user flag
- location JSON

The table intentionally has no model timestamps. `login_at` is the primary event timestamp.

**Previous:** [Troubleshooting](08-troubleshooting.md) | **Next:** [Data Flow](10-data-flow.md)
