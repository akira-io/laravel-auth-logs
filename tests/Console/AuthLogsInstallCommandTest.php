<?php

declare(strict_types=1);

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
        ->expectsOutput('Migrations published successfully in database/migrations/2025_01_23_201639_create_laravel_auth_logs_table.php')
        ->assertExitCode(0);
});

test('it can run the command silently', function (): void {

    $this->artisan('auth-logs:install --silent')
        ->assertExitCode(0);
});
