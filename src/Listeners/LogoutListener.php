<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Listeners;

use Illuminate\Auth\Events\Logout;

final class LogoutListener
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (! method_exists($user, 'registerLogout')) {
            return;
        }

        $user->registerLogout();
    }
}
