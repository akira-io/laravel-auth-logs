<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Concerns;

use Akira\LaravelAuthLogs\Actions\GetLocation;
use Akira\LaravelAuthLogs\ValueObjects\Location;
use Illuminate\Support\Collection;

trait InteractsWithLogs
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

        $location = $this->getStoredLocation();

        if ($location->isEmpty()) {
            $location = GetLocation::make($this->getIpAddress());
        }

        if ($location->isEmpty()) {
            return __('Unknown');
        }

        $this->log->update(['location' => $location->all()]);

        return Location::make($location)
            ->getFullLocation();
    }

    /**
     * @return Collection<string, mixed>
     */
    private function getStoredLocation(): Collection
    {

        $location = collect($this->log->location ?? []);

        if ($location->has('city') || $location->has('country')) {
            return $location;
        }

        return collect();
    }
}
