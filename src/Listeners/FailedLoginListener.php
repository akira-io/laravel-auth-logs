<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use Akira\LaravelAuthLogs\Actions\SendNotification;
use Akira\LaravelAuthLogs\Templates\FailedLogin;
use Illuminate\Auth\Events\Failed;

final class FailedLoginListener
{
    public function __construct() {}

    public function handle(Failed $event): void
    {

        $user = $event->user;

        $template = config('auth-logs.templates.failed_login.template', FailedLogin::class);

        $log = CreateAuthenticationLog::for($user);

        SendNotification::make(authenticatable: $user, template: $template, log: $log)->send();
    }
}
