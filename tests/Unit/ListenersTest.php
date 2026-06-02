<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Listeners\FailedLoginListener;
use Akira\LaravelAuthLogs\Listeners\LoginListener;
use Akira\LaravelAuthLogs\Listeners\LogoutListener;
use Akira\LaravelAuthLogs\Listeners\OtherDeviceLogoutListener;
use Akira\LaravelAuthLogs\Tests\Fixtures\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Support\Facades\Notification;

it('login listener logs and conditionally notifies', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    Notification::fake();

    $user = User::create(['email' => 'l@example.test', 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)]);

    request()->server->set('REMOTE_ADDR', '5.5.5.5');
    request()->headers->set('User-Agent', 'UA/5');
    request()->merge(['location' => []]);

    new LoginListener()->handle(new Login('web', $user, false));

    expect(AuthenticationLog::query()->count())->toBe(1);
});

it('login listener respects disabled new device notifications', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    config()->set('auth-logs.templates.new_device.notification', false);
    Notification::fake();

    $user = User::create(['email' => 'disabled-login@example.test', 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)]);

    request()->server->set('REMOTE_ADDR', '5.5.5.6');
    request()->headers->set('User-Agent', 'UA/5-disabled');
    request()->merge(['location' => []]);

    new LoginListener()->handle(new Login('web', $user, false));

    expect(AuthenticationLog::query()->count())->toBe(1);
    Notification::assertNothingSent();
});

it('failed login listener logs for user', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    Notification::fake();

    $user = User::create(['email' => 'f@example.test', 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)]);

    request()->server->set('REMOTE_ADDR', '6.6.6.6');
    request()->headers->set('User-Agent', 'UA/6');
    request()->merge(['location' => []]);

    new FailedLoginListener()->handle(new Failed('web', $user, []));

    expect(AuthenticationLog::query()->count())->toBe(1);
});

it('failed login listener respects disabled notifications', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    config()->set('auth-logs.templates.failed_login.notification', false);
    Notification::fake();

    $user = User::create(['email' => 'disabled-failed@example.test', 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)]);

    request()->server->set('REMOTE_ADDR', '6.6.6.7');
    request()->headers->set('User-Agent', 'UA/6-disabled');
    request()->merge(['location' => []]);

    new FailedLoginListener()->handle(new Failed('web', $user, []));

    expect(AuthenticationLog::query()->count())->toBe(1);
    Notification::assertNothingSent();
});

it('logout listener registers logout on the latest authentication log', function (): void {
    config()->set('auth-logs.db_connection', 'testing');

    $user = User::create(['email' => 'logout@example.test', 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)]);

    request()->server->set('REMOTE_ADDR', '7.7.7.7');
    request()->headers->set('User-Agent', 'UA/7');
    request()->merge(['location' => []]);

    $log = CreateAuthenticationLog::for($user, true);

    new LogoutListener()->handle(new Logout('web', $user));

    expect($log->fresh())
        ->logout_at->not->toBeNull()
        ->cleared_by_user->toBeTrue();
});

it('configured auth events are registered to their configured listeners', function (): void {
    $configuredListeners = [
        [(string) config('auth-logs.events.login'), (string) config('auth-logs.listeners.login')],
        [(string) config('auth-logs.events.failed'), (string) config('auth-logs.listeners.failed')],
        [(string) config('auth-logs.events.logout'), (string) config('auth-logs.listeners.logout')],
        [(string) config('auth-logs.events.logout-other-devices'), (string) config('auth-logs.listeners.other_device_logout')],
    ];

    $raw = app('events')->getRawListeners();

    foreach ($configuredListeners as [$event, $listener]) {
        expect($event)->not->toBeEmpty()
            ->and($listener)->not->toBeEmpty()
            ->and($raw)->toHaveKey($event)
            ->and($raw[$event])->toContain($listener);
    }
});

it('logout event is registered to LogoutListener independently', function (): void {
    $raw = app('events')->getRawListeners();

    expect($raw)->toHaveKey(Logout::class)
        ->and($raw[Logout::class])->toContain(LogoutListener::class)
        ->and($raw[Logout::class])->not->toContain(OtherDeviceLogoutListener::class);
});

it('other device logout event is registered to OtherDeviceLogoutListener independently', function (): void {
    $raw = app('events')->getRawListeners();

    expect($raw)->toHaveKey(OtherDeviceLogout::class)
        ->and($raw[OtherDeviceLogout::class])->toContain(OtherDeviceLogoutListener::class)
        ->and($raw[OtherDeviceLogout::class])->not->toContain(LogoutListener::class);
});
