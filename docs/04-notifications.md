# Notifications

The package automatically sends email notifications for important authentication events. This guide explains how notifications work and how to customize them.

## Built-in Notifications

### New Device Login

Triggered when a user successfully logs in from an unrecognized device (unique combination of IP address and user-agent).

**When it's sent:**
- User logs in successfully
- The IP + user-agent combination has never been seen before
- The user account is not newly created (older than 1 minute)

**Email content includes:**
- User email address
- Login timestamp (UTC+0)
- IP address
- User agent (browser/device info)
- Geolocation (city, country)

**Disable in `.env`:**
```env
AUTH_LOGS_NEW_DEVICE_NOTIFICATION=false
```

### Failed Login Attempt

Triggered when someone attempts to log in with incorrect credentials for an existing user account.

**When it's sent:**
- Authentication fails (wrong password)
- The user account exists

**Email content includes:**
- User email address
- Attempt timestamp (UTC+0)
- IP address
- User agent
- Geolocation

**Disable in `.env`:**
```env
AUTH_LOGS_FAILED_LOGIN_NOTIFICATION=false
```

## Notification Class

All authentication notifications use the `AuthLogsNotification` class, which implements Laravel's `ShouldQueue` interface for asynchronous delivery.

```php
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
```

The notification automatically determines delivery channels by calling the `notifyAuthenticationLogVia()` method on your User model.

## Customizing Notification Channels

### Per-User Channels

Override the `notifyAuthenticationLogVia()` method in your User model:

```php
class User extends Authenticatable
{
    use Notifiable, AuthLogs;

    public function notifyAuthenticationLogVia(): array
    {
        // Different channels based on user preferences
        $channels = ['mail'];

        if ($this->sms_notifications_enabled) {
            $channels[] = 'nexmo';
        }

        if ($this->slack_webhook) {
            $channels[] = 'slack';
        }

        return $channels;
    }
}
```

### Global Channels

Modify the configuration file:

```php
// config/auth-logs.php

'notification_via' => ['mail', 'slack'],
```

## Custom Templates

Create custom notification templates by implementing the `ToMail` contract.

### Creating a Custom Template

```php
<?php

namespace App\Notifications\AuthLogs;

use Akira\LaravelAuthLogs\Contracts\ToMail;
use Illuminate\Notifications\Messages\MailMessage;

final readonly class CustomNewDevice implements ToMail
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
            ->subject('Security Alert: New Device Login')
            ->greeting("Hello {$notifiable->name}!")
            ->line('We detected a login from a new device.')
            ->line("**Time:** {$this->loginAt}")
            ->line("**Location:** {$this->location}")
            ->line("**IP:** {$this->ipAddress}")
            ->line("**Device:** {$this->userAgent}")
            ->action('Review Account Security', url('/profile/security'))
            ->line('If this wasn\'t you, secure your account immediately.');
    }
}
```

### Register the Custom Template

Update the configuration:

```php
// config/auth-logs.php

'templates' => [
    'new_device' => [
        'notification' => true,
        'template' => \App\Notifications\AuthLogs\CustomNewDevice::class,
    ],
],
```

## Template Contract

All templates must implement the `Template` contract:

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

For email notifications, also implement `ToMail`:

```php
interface ToMail extends Template
{
    public function toMail(mixed $notifiable): MailMessage;
}
```

## Adding Support for Other Channels

### Slack Example

Implement a `toSlack()` method in your template:

```php
use Illuminate\Notifications\Messages\SlackMessage;

class CustomNewDevice implements ToMail
{
    // ... constructor ...

    public function toMail(mixed $notifiable): MailMessage
    {
        // ... email implementation ...
    }

    public function toSlack(mixed $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->warning()
            ->content('New device login detected')
            ->attachment(function ($attachment) use ($notifiable) {
                $attachment
                    ->title('Login Details')
                    ->fields([
                        'User' => $notifiable->email,
                        'Time' => $this->loginAt,
                        'Location' => $this->location,
                        'IP' => $this->ipAddress,
                    ]);
            });
    }
}
```

Then create a custom notification class:

```php
namespace App\Notifications;

use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;

class CustomAuthLogsNotification extends AuthLogsNotification
{
    public function toSlack(mixed $notifiable)
    {
        return $this->template->toSlack($notifiable);
    }
}
```

## Notification Queue

By default, authentication notifications are queued for asynchronous processing. Ensure your queue worker is running:

```bash
php artisan queue:work
```

For immediate delivery, remove the `ShouldQueue` implementation from a custom notification class.

## Testing Notifications

Test that notifications are sent correctly:

```php
use Illuminate\Support\Facades\Notification;

public function test_new_device_notification_sent()
{
    Notification::fake();

    $user = User::factory()->create();

    // Simulate login
    Auth::login($user);

    Notification::assertSentTo(
        $user,
        \Akira\LaravelAuthLogs\Notifications\AuthLogsNotification::class
    );
}
```

## Localization

Customize notification text by publishing translations:

```bash
php artisan vendor:publish --tag="laravel-auth-logs-translations"
```

Edit the translation file:

```json
{
    "New device login": "Novo dispositivo conectado",
    "Failed login attempt": "Tentativa de login falhada"
}
```

**Previous:** [Usage](03-usage.md) | **Next:** [Advanced Usage](05-advanced-usage.md)
