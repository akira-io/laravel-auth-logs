<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Listeners\FailedLoginListener;
use Akira\LaravelAuthLogs\Listeners\LoginListener;
use Akira\LaravelAuthLogs\Listeners\LogoutListener;
use Akira\LaravelAuthLogs\Tests\Fixtures\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
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

it('logout listener marks the latest log as logged out', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    Notification::fake();

    $user = User::create(['email' => 'lo@example.test', 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)]);

    request()->server->set('REMOTE_ADDR', '8.8.8.8');
    request()->headers->set('User-Agent', 'UA/8');
    request()->merge(['location' => []]);

    new LoginListener()->handle(new Login('web', $user, false));

    expect(AuthenticationLog::query()->first()->logout_at)->toBeNull();

    new LogoutListener()->handle(new Logout('web', $user));

    $log = AuthenticationLog::query()->first();

    expect($log->logout_at)->not->toBeNull()
        ->and($log->cleared_by_user)->toBeTrue();
});

it('logout listener skips when user does not implement registerLogout', function (): void {
    config()->set('auth-logs.db_connection', 'testing');

    $user = new class implements \Illuminate\Contracts\Auth\Authenticatable {
        use \Illuminate\Auth\Authenticatable;
    };

    expect(fn () => new LogoutListener()->handle(new Logout('web', $user)))->not->toThrow(Throwable::class);
});

