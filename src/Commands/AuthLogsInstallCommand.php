<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Commands;

use Illuminate\Console\Command;

final class AuthLogsInstallCommand extends Command
{
    public $signature = 'auth-logs:install';

    public $description = 'Install the Laravel Auth Logs package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {

        $this->info('Starting installation...');

        $this->info('Publishing configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'auth-logs-config', '--force' => true]);
        $this->info('Configuration published successfully in config/auth-logs.php');

        $this->info('Publishing migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'auth-logs-migrations', '--force' => true]);

        $this->info('Migrations published successfully in database/migrations/2025_01_23_201639_create_laravel_auth_logs_table.php');

        $this->info('Installation complete.');

        return self::SUCCESS;
    }
}
