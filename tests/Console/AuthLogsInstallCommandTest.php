<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

test('it can run the command', function (): void {

    $this->artisan('auth-logs:install')
        ->expectsOutput('Installation complete.')
        ->assertExitCode(0);
});

test('it publishes the configuration file', function (): void {

    $this->artisan('auth-logs:install')
        ->expectsOutput('Publishing configuration...')
        ->expectsOutput('Configuration published successfully in config/auth-logs.php')
        ->assertExitCode(0);

    $this->assertFileExists(config_path('auth-logs.php'));
});

test('it publishes the migration file', function (): void {

    $this->artisan('auth-logs:install')
        ->expectsOutput('Publishing migrations...')
        ->expectsOutput('Migrations published successfully in database/migrations')
        ->assertExitCode(0);
});

test('it can run the command silently', function (): void {

    $this->artisan('auth-logs:install --silent')
        ->assertExitCode(0);
});

test('the migration stub can roll back the configured table', function (): void {
    config()->set('auth-logs.table_name', 'authentication_logs_rollback_test');

    Schema::dropIfExists('authentication_logs_rollback_test');

    $migration = require __DIR__.'/../../database/migrations/create_laravel_auth_logs_table.php.stub';

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();

    expect(Schema::hasTable('authentication_logs_rollback_test'))->toBeTrue();

    $migration->down();

    expect(Schema::hasTable('authentication_logs_rollback_test'))->toBeFalse();
});

test('the migration stub declares rollback support', function (): void {
    $stub = file_get_contents(__DIR__.'/../../database/migrations/create_laravel_auth_logs_table.php.stub');

    expect($stub)
        ->toContain('public function down(): void')
        ->toContain('Schema::dropIfExists(config(\'auth-logs.table_name\'))');
});
