<?php

namespace Akira\LaravelAuthLogs\Commands;

use Illuminate\Console\Command;

class LaravelAuthLogsCommand extends Command
{
    public $signature = 'laravel-auth-logs';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
