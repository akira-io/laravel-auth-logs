<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

final class Device
{
    /**
     *  Check if the user has logged in before.
     */
    public static function isKnownFor(Authenticatable $user, ?string $ip, ?string $userAgent): ?AuthenticationLog
    {

        /** @var MorphMany<AuthenticationLog, Model> $logs */
        $logs = $user->authenticationLogs(); // @phpstan-ignore-line

        return $logs
            ->whereIpAddress($ip)
            ->whereUserAgent($userAgent)
            ->whereLoginSuccessful(true)
            ->first();
    }
}
