<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Actions\Device;
use Akira\LaravelAuthLogs\Actions\SendNotification;
use Akira\LaravelAuthLogs\Templates\NewDevice;
use Illuminate\Auth\Events\Login;

final class LoginListener
{
    public function __construct() {}

    public function handle(Login $event): void
    {

        $user = $event->user;

        $template = config('auth-logs.templates.new_device.template', NewDevice::class);

        $isKnown = Device::isKnownFor($user, request()->ip(), request()->userAgent());

        $log = CreateAuthenticationLog::for($user, isSuccessFull: true);

        if (! $isKnown && ! $user->isNew()) {
            SendNotification::make(authenticatable: $user, template: $template, log: $log)->send();
        }
    }
}
