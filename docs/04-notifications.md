# Notifications

Laravel Auth Logs sends queued mail notifications for two built-in events:

- new device login
- failed login attempt

Notifications are optional. Disabling them does not disable log creation.

## New Device Login

A new device notification is sent when all conditions are true:

- the user logs in successfully
- the same user does not have a prior successful log with the same IP address and user-agent
- the user is not considered new by `isNew()`
- `auth-logs.templates.new_device.notification` is truthy
- the configured template implements `ToMail`

The default mail includes account email, date, IP address, user agent, and location text.

Disable it with:

```env
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=false
```

## Failed Login Attempt

A failed login notification is sent when all conditions are true:

- Laravel dispatches a failed login event with a user instance
- `auth-logs.templates.failed_login.notification` is truthy
- the configured template implements `ToMail`

If the failed event has no user, the listener returns without creating a log or sending a notification.

Disable it with:

```env
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=false
```

## Delivery Channel

The built-in `AuthLogsNotification` supports only Laravel's `mail` channel.

```php
public function notifyAuthenticationLogVia(): array
{
    return ['mail'];
}
```

Returning any other channel from `notifyAuthenticationLogVia()` causes the built-in notification to throw a `RuntimeException`.

Use custom listeners and your own Laravel notification class for Slack, database, SMS, push, or any other channel.

## Queue Behavior

`AuthLogsNotification` implements `ShouldQueue`, so mail delivery is queued.

Run a queue worker in any environment where notifications should be delivered:

```bash
php artisan queue:work
```

The authentication log is created before notification dispatch. If the queue is stopped, log creation still happens, but mail jobs may remain pending.

## Template Contracts

`Template` defines the constructor data that the package injects:

```php
interface Template
{
    public function __construct(
        string $loginAt,
        string $ipAddress,
        string $location,
        string $userAgent,
    );
}
```

`ToMail` is required for built-in mail notifications:

```php
interface ToMail extends Template
{
    public function toMail(mixed $notifiable): MailMessage;
}
```

## Custom Mail Template

```php
<?php

namespace App\Notifications\AuthLogs;

use Akira\LaravelAuthLogs\Contracts\ToMail;
use Illuminate\Notifications\Messages\MailMessage;

final readonly class SecurityNewDevice implements ToMail
{
    public function __construct(
        private string $loginAt,
        private string $ipAddress,
        private string $location,
        private string $userAgent,
    ) {}

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Security alert: new device login')
            ->line('A new device logged into your account.')
            ->line("Date: {$this->loginAt}")
            ->line("IP address: {$this->ipAddress}")
            ->line("User agent: {$this->userAgent}")
            ->line("Location: {$this->location}");
    }
}
```

Register it:

```php
'templates' => [
    'new_device' => [
        'notification' => env('AUTH_LOGS_NEW_DEVICE_NOTIFICATION', true),
        'template' => \App\Notifications\AuthLogs\SecurityNewDevice::class,
    ],
],
```

## Custom Non-Mail Notifications

The package's notification class is `final`, so do not extend it. Replace the listener instead.

```php
<?php

namespace App\Listeners;

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use App\Notifications\SecuritySlackNotification;
use Illuminate\Auth\Events\Login;

final class NotifySecurityTeamOnLogin
{
    public function handle(Login $event): void
    {
        $log = CreateAuthenticationLog::for($event->user, isSuccessFull: true);

        $event->user->notify(new SecuritySlackNotification($log));
    }
}
```

Then configure the custom listener:

```php
'listeners' => [
    'login' => \App\Listeners\NotifySecurityTeamOnLogin::class,
],
```

## Location Text

Notification templates receive a string location. The package uses stored location data when available, otherwise it attempts a geolocation lookup. If no location can be resolved, the value is `Unknown`.

## Testing Notifications

```php
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
use Illuminate\Support\Facades\Notification;

Notification::fake();

// Trigger login or failed login.

Notification::assertSentTo($user, AuthLogsNotification::class);
```

For custom listeners, assert your custom notification class instead.

## Localization

Publish translations if you want to customize the built-in notification strings:

```bash
php artisan vendor:publish --tag="laravel-auth-logs-translations"
```

**Previous:** [Usage](03-usage.md) | **Next:** [Advanced Usage](05-advanced-usage.md)
