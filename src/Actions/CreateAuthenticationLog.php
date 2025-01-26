<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateAuthenticationLog
{
    /**
     * Create a new authentication log.
     */
    public static function for(Authenticatable $authenticatable, bool $isSuccessFull = false): AuthenticationLog
    {

        return $authenticatable->authenticationLogs() // @phpstan-ignore-line
            ->create([
                'login_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'location' => request()->location,
                'login_successful' => $isSuccessFull,
            ]);
    }
}
