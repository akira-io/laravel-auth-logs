# API Reference

Reference for public package classes, traits, contracts, and commands.

## Trait: `AuthLogs`

Namespace: `Akira\LaravelAuthLogs\Concerns\AuthLogs`

Add this trait to authenticatable models that should own authentication logs.

### `authenticationLogs()`

Returns a morph-many relationship to `AuthenticationLog`, ordered by latest `login_at`.

### `latestAuthentication()`

Returns the latest morph-one authentication log.

### `notifyAuthenticationLogVia(): array`

Returns `config('auth-logs.notification_via', ['mail'])`. Override on the model to customize the channel list. The built-in notification supports only `mail`.

### `lastLoginAt()`

Returns the `login_at` timestamp for the latest log, or `null`.

### `lastSuccessfulLoginAt()`

Returns the `login_at` timestamp for the latest successful log, or `null`.

### `lastLoginIp()`

Returns the IP address for the latest log, or `null`.

### `lastSuccessfulLoginIp()`

Returns the IP address for the latest successful log, or `null`.

### `previousLoginAt()`

Returns the `login_at` timestamp for the log before the latest one, or `null`.

### `previousLoginIp()`

Returns the IP address for the log before the latest one, or `null`.

### `registerLogout(): int|bool`

Updates the latest authentication log with `logout_at = now()` and `cleared_by_user = true`.

### `isNew(): bool`

Returns true when the model was created less than one minute ago. New device notifications use this to avoid notifying immediately after registration.

## Model: `AuthenticationLog`

Namespace: `Akira\LaravelAuthLogs\AuthenticationLog`

Final Eloquent model for a single authentication log. It has `$timestamps = false`.

Stored attributes:

- `id`
- `authenticatable_id`
- `authenticatable_type`
- `login_at`
- `login_successful`
- `ip_address`
- `user_agent`
- `location`
- `logout_at`
- `cleared_by_user`

The default migration does not create `created_at` or `updated_at`.

### `authenticatable()`

Returns the morph-to relationship for the owning authenticatable model.

### `getConnectionName(): string`

Uses `config('auth-logs.db_connection')`. If that value is `null`, falls back to `config('database.default')`.

### `getTable(): string`

Uses `config('auth-logs.table_name')`.

### Casts

- `login_at` to `datetime`
- `login_successful` to `boolean`
- `logout_at` to `datetime`
- `cleared_by_user` to `boolean`
- `location` to `array`

## Action: `CreateAuthenticationLog`

Namespace: `Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog`

### `for(Authenticatable $authenticatable, bool $isSuccessFull = false): AuthenticationLog`

Creates a log through `$authenticatable->authenticationLogs()`.

Captured values:

- `login_at` as `now()`
- `ip_address` from `request()->ip()`
- `user_agent` from `request()->userAgent()`
- `location` from `request()->location`
- `login_successful` from the second argument

The authenticatable model must provide the `authenticationLogs()` relationship, normally by using `AuthLogs`.

## Action: `Device`

Namespace: `Akira\LaravelAuthLogs\Actions\Device`

### `isKnownFor(Authenticatable $user, ?string $ip, ?string $userAgent): ?AuthenticationLog`

Returns the first successful log for the same user, IP address, and user-agent. Returns `null` when no match exists.

## Action: `GetLocation`

Namespace: `Akira\LaravelAuthLogs\Actions\GetLocation`

### `make(string $ip): Collection`

Returns geolocation data as a collection. Returns an empty collection when lookup is disabled or fails.

Endpoint behavior:

- `file://` appends `/{ip}` and reads JSON from disk.
- `data://` and `php://` are read directly.
- other schemes append `/{ip}` and use `file_get_contents()` with a 5 second timeout for HTTP/HTTPS context.

For non-file schemes, the decoded payload must have `status` equal to `success`.

## Action: `SendNotification`

Namespace: `Akira\LaravelAuthLogs\Actions\SendNotification`

### `make(Authenticatable $authenticatable, string $template, AuthenticationLog $log): self`

Creates a sender for the notifiable model, template class-string, and log.

### `send(): void`

Validates the inputs, verifies that the template class implements `ToMail`, and sends `AuthLogsNotification` through the authenticatable model's `notify()` method.

Throws `RuntimeException` for missing inputs or invalid template contracts.

## Listener: `LoginListener`

Handles `Illuminate\Auth\Events\Login`.

Behavior:

- checks whether the current IP and user-agent are known for the user
- creates a successful log
- sends a new device notification when enabled, the device is unknown, and the user is not new

## Listener: `FailedLoginListener`

Handles `Illuminate\Auth\Events\Failed`.

Behavior:

- returns immediately when the event has no user
- creates a failed log for an existing user
- sends a failed login notification when enabled

## Listener: `LogoutListener`

Handles `Illuminate\Auth\Events\Logout`.

Behavior:

- returns when the event user does not have `registerLogout()`
- otherwise updates the latest authentication log through `registerLogout()`

## Listener: `OtherDeviceLogoutListener`

Handles `Illuminate\Auth\Events\OtherDeviceLogout`.

The default class is empty and exists as a customization hook.

## Notification: `AuthLogsNotification`

Namespace: `Akira\LaravelAuthLogs\Notifications\AuthLogsNotification`

Final queued Laravel notification for mail delivery.

### Constructor

```php
public function __construct(
    ToMail|string $template,
    ?AuthenticationLog $log = null,
)
```

`$template` can be an already-built `ToMail` instance or a class-string implementing `ToMail`. When a class-string is used, `$log` is required so the template can be resolved later with login date, IP address, location, and user-agent.

### `via(mixed $notifiable): array`

Reads channels from `$notifiable->notifyAuthenticationLogVia()`. Only `mail` is accepted.

### `toMail(mixed $notifiable): MailMessage`

Resolves the template and delegates mail rendering to `ToMail::toMail()`.

## Contracts

### `Template`

Namespace: `Akira\LaravelAuthLogs\Contracts\Template`

```php
public function __construct(
    string $loginAt,
    string $ipAddress,
    string $location,
    string $userAgent,
);
```

### `ToMail`

Namespace: `Akira\LaravelAuthLogs\Contracts\ToMail`

Extends `Template` and requires:

```php
public function toMail(mixed $notifiable): MailMessage;
```

Built-in notification templates must implement this contract.

## Templates

### `NewDevice`

Namespace: `Akira\LaravelAuthLogs\Templates\NewDevice`

Mail template for unknown successful login devices.

### `FailedLogin`

Namespace: `Akira\LaravelAuthLogs\Templates\FailedLogin`

Mail template for failed login attempts tied to an existing user.

## Value Object: `Location`

Namespace: `Akira\LaravelAuthLogs\ValueObjects\Location`

Constructed from geolocation collection data. `getFullLocation()` returns `"City, Country"`.

Properties:

- `city`
- `country`
- `timezone`
- `latitude`
- `longitude`
- `isoCode`

## Command: `auth-logs:install`

Publishes package configuration and migration files.

```bash
php artisan auth-logs:install
```

## Service Provider

Namespace: `Akira\LaravelAuthLogs\LaravelAuthLogsServiceProvider`

Responsibilities:

- registers package config, views, translations, migration, and install command
- guards against non-array `auth-logs` config values during package registration
- subscribes configured auth events to configured listeners

**Previous:** [Advanced Usage](05-advanced-usage.md) | **Next:** [Testing](07-testing.md)
