<?php

namespace Akira\LaravelAuthLogs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Akira\LaravelAuthLogs\LaravelAuthLogs
 */
class LaravelAuthLogs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Akira\LaravelAuthLogs\LaravelAuthLogs::class;
    }
}
