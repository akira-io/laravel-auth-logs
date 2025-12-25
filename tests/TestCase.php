<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Tests;

use Akira\LaravelAuthLogs\LaravelAuthLogsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {

        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Akira\\LaravelAuthLogs\\Database\\Factories\\'.class_basename($modelName)
                .'Factory',
        );
    }

    final public function getEnvironmentSetUp($app): void
    {

        // Configure in-memory sqlite for tests
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Minimal users table for morph relations in tests
        Schema::create('users', function (Blueprint $table): void {

            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // Authentication logs table used by the package model
        Schema::create('authentication_logs', function (Blueprint $table): void {

            $table->id();
            $table->unsignedBigInteger('authenticatable_id');
            $table->string('authenticatable_type');
            $table->dateTime('login_at');
            $table->boolean('login_successful')->default(false);
            $table->string('ip_address');
            $table->string('user_agent');
            $table->json('location')->nullable();
            $table->dateTime('logout_at')->nullable();
            $table->boolean('cleared_by_user')->default(false);
        });
    }

    protected function getPackageProviders($app): array
    {

        return [
            LaravelAuthLogsServiceProvider::class,
        ];
    }
}
