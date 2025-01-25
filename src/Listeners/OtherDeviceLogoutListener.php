<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Illuminate\Auth\Events\OtherDeviceLogout;

final class OtherDeviceLogoutListener
{
    public function __construct() {}

    public function handle(OtherDeviceLogout $event): void {}
}
