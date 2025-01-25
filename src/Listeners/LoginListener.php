<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Illuminate\Auth\Events\Login;

final class LoginListener
{
    public function __construct() {}

    public function handle(Login $event): void {}
}
