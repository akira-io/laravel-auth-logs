<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateAuthenticationLog
{
    public static function for(Authenticatable $authenticatable, bool $isSuccessFull = false): AuthenticationLog
    {

        return $authenticatable->authenticationLogs()->create([
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'location' => request()->location,
            'login_successful' => $isSuccessFull,
        ]);
    }
}
