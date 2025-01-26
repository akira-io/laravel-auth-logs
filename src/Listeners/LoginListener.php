<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Actions\Device;
use Akira\LaravelAuthLogs\Actions\SendNotification;
use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Templates\NewDevice;
use Illuminate\Auth\Events\Login;

final class LoginListener
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {

        $user = $event->user;

        $template = config('auth-logs.templates.new_device.template', NewDevice::class);

        $template = type($template)->asString();

        $isKnown = Device::isKnownFor($user, request()->ip(), request()->userAgent());

        $log = CreateAuthenticationLog::for($user, isSuccessFull: true);

        // @phpstan-ignore-next-line
        if (! $isKnown instanceof AuthenticationLog && ! $user->isNew()) {
            SendNotification::make(authenticatable: $user, template: $template, log: $log)->send();
        }
    }
}
