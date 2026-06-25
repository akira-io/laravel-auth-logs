# API Reference

Complete reference for all public classes, traits, contracts, and methods provided by the package.

## Traits

### `Akira\LaravelAuthLogs\Concerns\AuthLogs`

Add to your authenticatable model to enable authentication logging.

#### Relationships

##### `authenticationLogs()`
```php
public function authenticationLogs(): MorphMany
```
Returns all authentication logs for the user, ordered by most recent first.

##### `latestAuthentication()`
```php
public function latestAuthentication(): MorphOne
```
Returns the most recent authentication log entry.

#### Query Methods

##### `lastLoginAt()`
```php
public function lastLoginAt(): ?Carbon
```
Returns the timestamp of the last login attempt (successful or failed).

##### `lastSuccessfulLoginAt()`
```php
public function lastSuccessfulLoginAt(): ?Carbon
```
Returns the timestamp of the last successful login.

##### `lastLoginIp()`
```php
public function lastLoginIp(): ?string
```
Returns the IP address of the last login attempt.

##### `lastSuccessfulLoginIp()`
```php
public function lastSuccessfulLoginIp(): ?string
```
Returns the IP address of the last successful login.

##### `previousLoginAt()`
```php
public function previousLoginAt(): ?Carbon
```
Returns the timestamp of the login before the most recent one.

##### `previousLoginIp()`
```php
public function previousLoginIp(): ?string
```
Returns the IP address of the previous login.

#### Action Methods

##### `registerLogout()`
```php
public function registerLogout(): int|bool
```
Updates the latest authentication log with the logout timestamp and marks it as cleared by user.

##### `isNew()`
```php
public function isNew(): bool
```
Returns `true` if the user was created less than 1 minute ago.

##### `notifyAuthenticationLogVia()`
```php
public function notifyAuthenticationLogVia(): array
```
Returns the notification channels to use. Override this method to customize per-user channels.

## Models

### `Akira\LaravelAuthLogs\AuthenticationLog`

Eloquent model representing a single authentication log entry.

#### Properties

```php
public int $id
public int $authenticatable_id
public string $authenticatable_type
public Carbon $login_at
public bool $login_successful
public string $ip_address
public string $user_agent
public array $location
public ?Carbon $logout_at
public bool $cleared_by_user
public ?Carbon $created_at
public ?Carbon $updated_at
```

#### Relationships

##### `authenticatable()`
```php
public function authenticatable(): MorphTo
```
Returns the authenticatable model (User) associated with this log.

#### Methods

##### `getConnectionName()`
```php
public function getConnectionName(): string
```
Returns the database connection name from configuration.

##### `getTable()`
```php
public function getTable(): string
```
Returns the table name from configuration.

## Actions

### `Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog`

Create authentication log entries.

##### `for()`
```php
public static function for(
    Authenticatable $authenticatable,
    bool $isSuccessFull = false
): AuthenticationLog
```
Creates a new authentication log for the given user with current request context.

### `Akira\LaravelAuthLogs\Actions\SendNotification`

Send authentication notifications.

##### `make()`
```php
public static function make(
    Authenticatable $authenticatable,
    string $template,
    AuthenticationLog $log
): self
```
Creates a new notification sender instance.

##### `send()`
```php
public function send(): void
```
Sends the notification through configured channels.

### `Akira\LaravelAuthLogs\Actions\GetLocation`

Fetch geolocation data for IP addresses.

##### `make()`
```php
public static function make(string $ip): Collection
```
Fetches geolocation data for the given IP address. Returns empty collection on failure.

### `Akira\LaravelAuthLogs\Actions\Device`

Check device recognition.

##### `isKnownFor()`
```php
public static function isKnownFor(
    Authenticatable $user,
    ?string $ip,
    ?string $userAgent
): ?AuthenticationLog
```
Returns the first authentication log matching the IP and user-agent, or `null` if device is unknown.

## Listeners

### `Akira\LaravelAuthLogs\Listeners\LoginListener`

Handles successful login events.

##### `handle()`
```php
public function handle(Login $event): void
```
Creates authentication log and sends notification if device is new.

### `Akira\LaravelAuthLogs\Listeners\FailedLoginListener`

Handles failed login events.

##### `handle()`
```php
public function handle(Failed $event): void
```
Creates authentication log and sends notification for failed attempts.

### `Akira\LaravelAuthLogs\Listeners\LogoutListener`

Handles logout events.

##### `handle()`
```php
public function handle(Logout $event): void
```
Marks the latest authentication log as logged out when the authenticated model supports logout registration.

### `Akira\LaravelAuthLogs\Listeners\OtherDeviceLogoutListener`

Handles multi-device logout events.

Currently a placeholder for custom handling.

## Notifications

### `Akira\LaravelAuthLogs\Notifications\AuthLogsNotification`

Notification class for authentication events. Implements `ShouldQueue`.

##### Constructor
```php
public function __construct(Template $template)
```

##### `via()`
```php
public function via(mixed $notifiable): array
```
Returns notification channels from the notifiable's `notifyAuthenticationLogVia()` method.

##### `toMail()`
```php
public function toMail(mixed $notifiable): MailMessage
```
Delegates to the template's `toMail()` method.

## Templates

### `Akira\LaravelAuthLogs\Templates\NewDevice`

Template for new device login notifications.

##### Constructor
```php
public function __construct(
    string $loginAt,
    string $ipAddress,
    string $location,
    string $userAgent
)
```

##### `toMail()`
```php
public function toMail(mixed $notifiable): MailMessage
```
Returns a MailMessage with new device notification content.

### `Akira\LaravelAuthLogs\Templates\FailedLogin`

Template for failed login attempt notifications.

##### Constructor
```php
public function __construct(
    string $loginAt,
    string $ipAddress,
    string $location,
    string $userAgent
)
```

##### `toMail()`
```php
public function toMail(mixed $notifiable): MailMessage
```
Returns a MailMessage with failed login notification content.

## Contracts

### `Akira\LaravelAuthLogs\Contracts\Template`

Base interface for notification templates.

```php
interface Template
{
    public function __construct(
        string $loginAt,
        string $ipAddress,
        string $location,
        string $userAgent
    );
}
```

### `Akira\LaravelAuthLogs\Contracts\ToMail`

Interface for email notification templates.

```php
interface ToMail extends Template
{
    public function toMail(mixed $notifiable): MailMessage;
}
```

## Value Objects

### `Akira\LaravelAuthLogs\ValueObjects\Location`

Represents geolocation data.

#### Properties

```php
public readonly string $city
public readonly string $country
public readonly string $timezone
public readonly string $latitude
public readonly string $longitude
public readonly string $isoCode
```

#### Methods

##### `make()`
```php
public static function make(Collection $data): self
```
Creates a Location instance from geolocation API response.

##### `getFullLocation()`
```php
public function getFullLocation(): string
```
Returns formatted location string: "City, Country".

## Commands

### `php artisan auth-logs:install`

Installation command that publishes configuration and migrations.

**Signature:** `auth-logs:install`

**Actions:**
- Publishes `config/auth-logs.php`
- Publishes migration file

## Configuration Keys

Reference for all `config/auth-logs.php` options:

- `table_name` (string): Database table name
- `date_format` (string): PHP date format for notifications
- `geolocation_api` (string): Geolocation API endpoint
- `db_connection` (string|null): Database connection name
- `notification_via` (array): Notification channels
- `events` (array): Laravel authentication event classes
- `listeners` (array): Event listener classes
- `templates` (array): Notification template configuration
- `purge` (int|null): Log retention period in days

## Service Provider

### `Akira\LaravelAuthLogs\LaravelAuthLogsServiceProvider`

Package service provider. Automatically registers event listeners.

##### `configurePackage()`
```php
public function configurePackage(Package $package): void
```
Configures package resources and registers event listeners.

**Previous:** [Advanced Usage](05-advanced-usage.md) | **Next:** [Testing](07-testing.md)
