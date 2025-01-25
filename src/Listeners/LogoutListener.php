<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Illuminate\Auth\Events\Logout;

final class LogoutListener
{
    public function __construct() {}

    public function handle(Logout $event): void {}
}
