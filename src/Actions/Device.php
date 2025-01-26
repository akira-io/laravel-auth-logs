<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Illuminate\Contracts\Auth\Authenticatable;

final class Device
{
    /**
     *  Check if the user has logged in before.
     */
    public static function isKnownFor(Authenticatable $user, $ip, ?string $userAgent): ?AuthenticationLog
    {

        return $user->authenticationLogs()->whereIpAddress($ip)
            ->whereUserAgent($userAgent)
            ->whereLoginSuccessful(true)
            ->first();
    }
}