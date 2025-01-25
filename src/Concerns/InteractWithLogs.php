<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Concerns;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\ValueObjects\Location;

trait InteractWithLogs
{
    public function __construct(public AuthenticationLog $authenticationLog) {}

    final public static function make(AuthenticationLog $authenticationLog): static
    {

        return app(static::class, ['authenticationLog' => $authenticationLog]);
    }

    public function getLoginAt(): string
    {

        return $this
            ->authenticationLog
            ->login_at
            ->format(config('project.datetime_format'));
    }

    public function getIpAddress(): string
    {

        return $this
            ->authenticationLog
            ->ip_address;
    }

    public function getUserAgent(): string
    {

        return $this
            ->authenticationLog
            ->user_agent;
    }

    public function getFullLocation(): string
    {

        return Location::make($this->authenticationLog->location)
            ->getFullLocation();
    }
}
