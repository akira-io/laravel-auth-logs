<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\ValueObjects;

use Illuminate\Support\Collection;

final readonly class Location
{
    public string $city;

    public string $country;

    public string $timezone;

    public string|float $latitude;

    public string|float $longitude;

    public string $isoCode;

    /**
     * Construct a new location.
     */
    public function __construct(Collection $data)
    {

        $this->isoCode = data_get($data, 'countryCode');
        $this->country = data_get($data, 'country');
        $this->city = data_get($data, 'city');
        $this->latitude = data_get($data, 'lat');
        $this->longitude = data_get($data, 'lon');
        $this->timezone = data_get($data, 'timezone');
    }

    /**
     * Make a new location.
     */
    public static function make(Collection $data): self
    {

        return app(self::class, ['data' => $data]);
    }

    /**
     * Get the full location.
     */
    public function getFullLocation(): string
    {

        return "{$this->city}, {$this->country}";
    }
}
