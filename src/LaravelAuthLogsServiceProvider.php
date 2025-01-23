<?php

namespace Akira\LaravelAuthLogs;

use Akira\LaravelAuthLogs\Commands\AuthLogsInstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAuthLogsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-auth-logs')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_auth_logs_table')
            ->hasCommand(AuthLogsInstallCommand::class);
    }
}
