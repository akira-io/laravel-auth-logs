<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
use Akira\LaravelAuthLogs\Templates\FailedLogin;
use Illuminate\Auth\Events\Failed;

final class FailedLoginListener
{
    public function __construct() {}

    public function handle(Failed $event): void
    {

        $user = $event->user;

        $template = config('auth-logs.templates.failed_login', FailedLogin::class);

        $user->authenticationLogs()->create([
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'location' => request()->location,
            'is_successful' => false,
        ]);

        $user->notify(new AuthLogsNotification($template));
    }
}
