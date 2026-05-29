<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Actions\Device;
use Akira\LaravelAuthLogs\Actions\GetLocation;
use Akira\LaravelAuthLogs\Actions\SendNotification;
use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
use Akira\LaravelAuthLogs\Templates\NewDevice;
use Akira\LaravelAuthLogs\Tests\Fixtures\User;
use Illuminate\Support\Facades\Notification;

it('creates an authentication log via action', function (): void {
    config()->set('auth-logs.db_connection', 'testing');

    request()->server->set('REMOTE_ADDR', '1.1.1.1');
    request()->headers->set('User-Agent', 'Agent/1.0');
    request()->merge(['location' => ['iso_code' => 'WW']]);

    $user = User::query()->create([
        'name' => 'John',
        'email' => 'john@example.test',
        'created_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);

    $log = CreateAuthenticationLog::for($user, true);

    expect($log)->toBeInstanceOf(AuthenticationLog::class)
        ->and($log->login_successful)->toBeTrue()
        ->and($log->ip_address)->toBe('1.1.1.1')
        ->and($log->user_agent)->toBe('Agent/1.0');
});

it('detects known device for user', function (): void {
    config()->set('auth-logs.db_connection', 'testing');

    $user = User::create([
        'email' => 'known@example.test',
        'created_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);

    request()->server->set('REMOTE_ADDR', '2.2.2.2');
    request()->headers->set('User-Agent', 'UA/2.0');
    request()->merge(['location' => ['iso_code' => 'WW']]);
    CreateAuthenticationLog::for($user, true);

    $known = Device::isKnownFor($user, '2.2.2.2', 'UA/2.0');

    expect($known)->toBeInstanceOf(AuthenticationLog::class);
});

it('gets location data from local fixture and builds notification', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    $fixtures = realpath(__DIR__.'/../Fixtures') ?: realpath(__DIR__.'/../fixtures');
    config()->set('auth-logs.geolocation_api', 'file://'.$fixtures);

    Notification::fake();

    $user = User::create([
        'email' => 'jane@example.test',
        'created_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);

    request()->server->set('REMOTE_ADDR', '1.1.1.1');
    request()->headers->set('User-Agent', 'UA/1.0');
    request()->merge(['location' => ['status' => 'success']]);

    $log = CreateAuthenticationLog::for($user, true);

    $sender = SendNotification::make($user, NewDevice::class, $log);

    expect($sender->getIpAddress())->toBe('1.1.1.1')
        ->and($sender->getUserAgent())->toBe('UA/1.0')
        ->and($sender->getFullLocation())->toBe('Emerald City, Wonderland')
        ->and($sender->getLoginAt())->toBeString();

    $sender->send();

    Notification::assertSentTo($user, AuthLogsNotification::class);
});

it('returns empty collection when geolocation fails', function (): void {
    config()->set('auth-logs.geolocation_api', 'file:///path/does/not/exist');

    $result = GetLocation::make('9.9.9.9');

    expect($result->isEmpty())->toBeTrue();
});

it('returns empty collection when geolocation is disabled', function (): void {
    $geolocationApi = config('auth-logs.geolocation_api');

    try {
        config(['auth-logs.geolocation_api' => null]);

        $result = GetLocation::make('9.9.9.9');

        expect($result->isEmpty())->toBeTrue();
    } finally {
        config(['auth-logs.geolocation_api' => $geolocationApi]);
    }
});

it('validates send notification properties', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    $user = User::create(['email' => 'err@example.test']);
    request()->server->set('REMOTE_ADDR', '3.3.3.3');
    request()->headers->set('User-Agent', 'UA/3.0');
    request()->merge(['location' => []]);
    $log = CreateAuthenticationLog::for($user, false);

    $ref = new \ReflectionClass(SendNotification::class);
    $ctor = $ref->getConstructor();
    $instance = $ref->newInstanceWithoutConstructor();
    $ctor->invoke($instance, $user, '', $log);

    $method = new \ReflectionMethod($instance, 'send');

    expect(fn (): mixed => $method->invoke($instance))
        ->toThrow(RuntimeException::class);

    $noAuth = $ref->newInstanceWithoutConstructor();
    $sendNoAuth = new \ReflectionMethod($noAuth, 'send');
    expect(fn (): mixed => $sendNoAuth->invoke($noAuth))
        ->toThrow(RuntimeException::class);

    $missingLog = $ref->newInstanceWithoutConstructor();
    $pa = new \ReflectionProperty(SendNotification::class, 'authenticatable');
    $pt = new \ReflectionProperty(SendNotification::class, 'template');
    $pa->setValue($missingLog, $user);
    $pt->setValue($missingLog, NewDevice::class);

    $sendMissingLog = new \ReflectionMethod($missingLog, 'send');
    expect(fn (): mixed => $sendMissingLog->invoke($missingLog))
        ->toThrow(RuntimeException::class);
});
