<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;

final class AuthLogsInstallCommand extends Command
{
    public $signature = 'auth-logs:install';

    public $description = 'Install the Laravel Auth Logs package';

    public function handle(): int
    {
        info('Starting installation...');

        info('Publishing configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'auth-logs-config', '--force' => true]);

        info('Publishing migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'auth-logs-migrations', '--force' => true]);

        $this->info('Installation complete.');

        return self::SUCCESS;
    }
}
