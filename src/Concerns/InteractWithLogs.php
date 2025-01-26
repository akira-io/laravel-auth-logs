<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Concerns;

use Akira\LaravelAuthLogs\Actions\GetLocation;
use Akira\LaravelAuthLogs\ValueObjects\Location;

trait InteractWithLogs
{
    /**
     *  Get the login date.
     */
    public function getLoginAt(): string
    {

        return $this
            ->log
            ->login_at->format(type(config('auth-logs.date_format'))->asString());
    }

    /**
     *  Get the IP address.
     */
    public function getIpAddress(): string
    {

        return $this
            ->log
            ->ip_address;
    }

    /**
     *  Get the user agent.
     */
    public function getUserAgent(): string
    {

        return $this
            ->log
            ->user_agent;
    }

    /**
     *  Get the location.
     */
    public function getFullLocation(): string
    {

        $location = GetLocation::make($this->getIpAddress());

        if ($location->isEmpty()) {
            return __('Unknown');
        }

        return Location::make($location)
            ->getFullLocation();
    }
}
