<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Tests\Fixtures\User;

it('auth logs trait accessors work', function (): void {
    config()->set('auth-logs.db_connection', 'testing');

    $user = User::create(['email' => 't@example.test', 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)]);

    request()->server->set('REMOTE_ADDR', '7.7.7.7');
    request()->headers->set('User-Agent', 'UA/7');
    request()->merge(['location' => []]);

    CreateAuthenticationLog::for($user, true);
    CreateAuthenticationLog::for($user, true);

    expect($user->lastLoginAt())->not()->toBeNull()
        ->and($user->lastSuccessfulLoginAt())->not()->toBeNull()
        ->and($user->lastLoginIp())->toBe('7.7.7.7')
        ->and($user->lastSuccessfulLoginIp())->toBe('7.7.7.7')
        ->and($user->previousLoginAt())->not()->toBeNull()
        ->and($user->previousLoginIp())->toBe('7.7.7.7')
        ->and((bool) $user->registerLogout())->toBeTrue()
        ->and($user->isNew())->toBeFalse();
});
