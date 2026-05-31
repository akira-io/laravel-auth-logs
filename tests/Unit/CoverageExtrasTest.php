<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\Actions\GetLocation;
use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Actions\SendNotification;
use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Facades\LaravelAuthLogs;
use Akira\LaravelAuthLogs\Listeners\FailedLoginListener;
use Akira\LaravelAuthLogs\Listeners\LogoutListener;
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
use Akira\LaravelAuthLogs\Templates\NewDevice;
use Akira\LaravelAuthLogs\Tests\Fixtures\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable;

it('facade accessor returns underlying class', function (): void {

    $ref = new ReflectionClass(LaravelAuthLogs::class);
    $m = $ref->getMethod('getFacadeAccessor');
    $result = $m->invoke(null);
    expect($result)->toBe(Akira\LaravelAuthLogs\LaravelAuthLogs::class);
});

it('logout listener handle executes', function (): void {

    config()->set('auth-logs.db_connection', 'testing');

    $user = User::create(['email' => 'logout-listener@example.test']);

    $listener = new LogoutListener();
    $listener->handle(new Logout('web', $user));
    $listener->handle(new Logout('web', new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): null
        {
            return null;
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getRememberToken(): null
        {
            return null;
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return 'remember_token';
        }
    }));

    expect(true)->toBeTrue();
});

it('notification channels and toMail are returned', function (): void {

    $user = new User(['email' => 'n@example.test']);
    $notification = new AuthLogsNotification(new NewDevice('2025-01-01 00:00:00', '127.0.0.1', 'Nowhere', 'UA'));
    $channels = $notification->via($user);
    $mail = $notification->toMail($user);
    expect($channels)->toContain('mail');
    // ensure we hit the template path as well
    expect(method_exists($mail, 'render'))->toBeTrue();
});

it('notification rejects unsupported channels', function (): void {

    $notification = new AuthLogsNotification(new NewDevice('2025-01-01 00:00:00', '127.0.0.1', 'Nowhere', 'UA'));
    $notifiable = new class
    {
        public function notifyAuthenticationLogVia(): array
        {
            return ['slack'];
        }
    };

    expect(fn (): array => $notification->via($notifiable))
        ->toThrow(\RuntimeException::class, 'Laravel Auth Logs only supports the mail notification channel by default.');
});

it('authentication log morph relation resolves authenticatable', function (): void {

    config()->set('auth-logs.db_connection', 'testing');
    $user = User::create([
        'email' => 'morph@example.test', 'created_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);
    request()->server->set('REMOTE_ADDR', '8.8.8.8');
    request()->headers->set('User-Agent', 'UA/8');
    request()->merge(['location' => []]);

    $log = $user->authenticationLogs()->create([
        'login_at' => now(),
        'ip_address' => '8.8.8.8',
        'user_agent' => 'UA/8',
        'location' => [],
        'login_successful' => true,
    ]);

    expect($log)->toBeInstanceOf(AuthenticationLog::class)
        ->and($log->authenticatable->getKey())->toBe($user->getKey());
});

it('non-file geolocation path covers non-file branch', function (): void {

    // Use php://temp to go through the non-file branch and return empty
    config()->set('auth-logs.geolocation_api', 'php://temp');
    $collection = GetLocation::make('anything');
    expect($collection->isEmpty())->toBeTrue();

    // Also invoke the private fetch method to cover the resource path
    $ref = new ReflectionClass(GetLocation::class);
    $m = $ref->getMethod('fetchGeolocationData');
    $data = $m->invoke(null, 'php://temp');
    expect($data)->toBeNull();
});

it('non-file geolocation with failing status covers conditional', function (): void {

    // data:// stream returns a valid JSON with status != success
    $payload = base64_encode(json_encode(['status' => 'fail', 'message' => 'bad']));
    config()->set('auth-logs.geolocation_api', 'data://text/plain;base64,'.$payload);

    $collection = GetLocation::make('ignored');
    expect($collection->isEmpty())->toBeTrue();
});

it('non-file geolocation with success status covers success branch', function (): void {

    $dir = realpath(__DIR__.'/../Fixtures') ?: realpath(__DIR__.'/../fixtures');
    config()->set('auth-logs.geolocation_api', 'file://'.$dir);
    $collection = GetLocation::make('1.1.1.1');
    expect($collection->isEmpty())->toBeFalse();
});

it('http-like scheme covers else apiEndpoint path', function (): void {

    // Use an unsupported wrapper to avoid network while hitting the else branch
    config()->set('auth-logs.geolocation_api', 'gopher://nowhere');
    $collection = GetLocation::make('1.2.3.4');
    expect($collection->isEmpty())->toBeTrue();
});

it('failed login listener ignores null user', function (): void {

    $listener = new FailedLoginListener();
    $event = new Failed('web', null, []);
    $listener->handle($event);
    expect(true)->toBeTrue();
});

it('data scheme with success status covers non-file success branch', function (): void {

    $payload = base64_encode(json_encode(['status' => 'success', 'country' => 'X', 'city' => 'Y']));
    config()->set('auth-logs.geolocation_api', 'data://text/plain;base64,'.$payload);

    $collection = GetLocation::make('ignored');
    expect($collection->isEmpty())->toBeFalse();
});

it('interacts trait returns Unknown when location cannot be resolved', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    // Force empty geolocation result
    config()->set('auth-logs.geolocation_api', 'php://temp');

    $user = \Akira\LaravelAuthLogs\Tests\Fixtures\User::create([
        'email' => 'unknown@example.test',
        'created_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);

    request()->server->set('REMOTE_ADDR', '9.9.9.9');
    request()->headers->set('User-Agent', 'UA/X');
    request()->merge(['location' => []]);

    $log = CreateAuthenticationLog::for($user, true);

    $sender = SendNotification::make($user, \Akira\LaravelAuthLogs\Templates\NewDevice::class, $log);

    expect($sender->getFullLocation())->toBe(__('Unknown'));
});
