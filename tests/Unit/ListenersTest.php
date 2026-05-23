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

