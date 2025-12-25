<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Templates\FailedLogin;
use Akira\LaravelAuthLogs\Templates\NewDevice;
use Akira\LaravelAuthLogs\Tests\Fixtures\User;

it('authentication log casts, table and connection respond', function (): void {
    config()->set('auth-logs.db_connection', 'testing');
    config()->set('auth-logs.table_name', 'authentication_logs');

    $model = new AuthenticationLog();

    expect($model->getConnectionName())->toBe('testing')
        ->and($model->getTable())->toBe('authentication_logs');
});

it('templates render a mail message', function (): void {
    $user = new User(['email' => 't@example.test']);

    $nd = new NewDevice('2025-01-01 00:00:00', '127.0.0.1', 'Nowhere', 'UA');
    $fl = new FailedLogin('2025-01-01 00:00:00', '127.0.0.1', 'Nowhere', 'UA');

    $mail1 = $nd->toMail($user);
    $mail2 = $fl->toMail($user);

    expect(method_exists($mail1, 'render'))
        ->toBeTrue()
        ->and(method_exists($mail2, 'render'))
        ->toBeTrue();
});

