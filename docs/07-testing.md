# Testing

The package uses Pest, Orchestra Testbench, Pint, Rector, PHPStan, Pest type coverage, and Pest coverage.

## Full Gate

Run the same project gate used by CI:

```bash
composer test
```

The script runs:

- `pint --test`
- `rector --dry-run`
- `phpstan analyse`
- `pest --type-coverage --min=100 --compact`
- `pest --parallel --coverage --exactly=100 --compact`

Coverage requires a PHP coverage driver such as Xdebug or PCOV.

## Testbench Setup

Package tests extend `Akira\LaravelAuthLogs\Tests\TestCase`. The test case:

- registers `LaravelAuthLogsServiceProvider`
- configures an in-memory SQLite connection
- creates `users` and `authentication_logs` tables
- resets invalid `auth-logs` config state before bootstrapping

Use the same setup for package-level tests that need service-provider behavior.

## Testing Log Creation

```php
use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Tests\Fixtures\User;

config()->set('auth-logs.db_connection', 'testing');

$user = User::query()->create([
    'email' => 'john@example.test',
    'created_at' => now()->subMinutes(10),
    'updated_at' => now()->subMinutes(10),
]);

request()->server->set('REMOTE_ADDR', '1.1.1.1');
request()->headers->set('User-Agent', 'Agent/1.0');
request()->merge(['location' => []]);

$log = CreateAuthenticationLog::for($user, true);

expect($log->login_successful)->toBeTrue();
```

## Testing Listeners

Successful login:

```php
use Akira\LaravelAuthLogs\Listeners\LoginListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Notification;

Notification::fake();

new LoginListener()->handle(new Login('web', $user, false));

expect($user->authenticationLogs()->count())->toBe(1);
```

Failed login:

```php
use Akira\LaravelAuthLogs\Listeners\FailedLoginListener;
use Illuminate\Auth\Events\Failed;

new FailedLoginListener()->handle(new Failed('web', $user, []));

expect($user->authenticationLogs()->where('login_successful', false)->count())->toBe(1);
```

Failed login with no user does not create a log:

```php
new FailedLoginListener()->handle(new Failed('web', null, []));
```

Logout:

```php
use Akira\LaravelAuthLogs\Listeners\LogoutListener;
use Illuminate\Auth\Events\Logout;

new LogoutListener()->handle(new Logout('web', $user));

expect($user->latestAuthentication()->first()?->logout_at)->not->toBeNull();
```

## Testing Notifications

```php
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
use Illuminate\Support\Facades\Notification;

Notification::fake();

// Trigger login or failed login.

Notification::assertSentTo($user, AuthLogsNotification::class);
```

To test unsupported channels:

```php
$notifiable = new class
{
    public function notifyAuthenticationLogVia(): array
    {
        return ['slack'];
    }
};

expect(fn () => $notification->via($notifiable))
    ->toThrow(RuntimeException::class);
```

## Testing Geolocation

Use `file://` fixtures for deterministic geolocation tests:

```php
$fixtures = realpath(__DIR__.'/../Fixtures');

config()->set('auth-logs.geolocation_api', 'file://'.$fixtures);

$location = GetLocation::make('1.1.1.1');

expect($location)->toHaveKey('city');
```

Use `php://temp` or `null` to test empty geolocation behavior:

```php
config()->set('auth-logs.geolocation_api', 'php://temp');

expect(GetLocation::make('9.9.9.9')->isEmpty())->toBeTrue();
```

## Testing Custom Templates

Built-in notification templates must implement `ToMail`:

```php
expect(is_subclass_of(MyTemplate::class, ToMail::class))->toBeTrue();
```

When testing a custom `ToMail`, instantiate it with the four constructor strings and assert that `toMail()` returns a `MailMessage`.

## Random Order and Parallel Runs

The coverage command runs tests in parallel and Pest can randomize order. Tests that mutate config should set the required config values inside each test and avoid relying on previous test state.

If a test only fails in one random order, rerun with the seed shown by Pest and isolate shared config or service-provider state.

**Previous:** [API Reference](06-api-reference.md) | **Next:** [Troubleshooting](08-troubleshooting.md)
