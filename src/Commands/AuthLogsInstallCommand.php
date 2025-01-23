<?php

namespace Akira\LaravelAuthLogs\Commands;

use Illuminate\Console\Command;

class AuthLogsInstallCommand extends Command
{
    public $signature = 'auth-logs:install';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
