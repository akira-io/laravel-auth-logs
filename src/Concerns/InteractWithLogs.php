<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Concerns;

use Akira\LaravelAuthLogs\Actions\GetLocation;
use Akira\LaravelAuthLogs\ValueObjects\Location;

trait InteractWithLogs
{
    public function getLoginAt(): string
    {

        return $this
            ->log
            ->login_at->format(config('auth-logs.date_format'));
    }

    public function getIpAddress(): string
    {

        return $this
            ->log
            ->ip_address;
    }

    public function getUserAgent(): string
    {

        return $this
            ->log
            ->user_agent;
    }

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
