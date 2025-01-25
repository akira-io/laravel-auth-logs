<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\ValueObjects;

final readonly class Location
{
    public string $ip;

    public string $city;

    public string $country;

    public string $timezone;

    public string|float $latitude;

    public string|float $longitude;

    public string $isoCode;

    /**
     * Construct a new location.
     */
    public function __construct(array $data)
    {

        $this->ip = data_get($data, 'ip', $this->getUnknown());
        $this->isoCode = data_get($data, 'iso_code', $this->getUnknown());
        $this->country = data_get($data, 'country', $this->getUnknown());
        $this->city = data_get($data, 'city', $this->getUnknown());
        $this->latitude = data_get($data, 'latitude', $this->getUnknown());
        $this->longitude = data_get($data, 'longitude', $this->getUnknown());
        $this->timezone = data_get($data, 'timezone', $this->getUnknown());
    }

    /**
     * Make a new location.
     */
    public static function make(array $data): self
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

    /**
     * Get the unknown value.
     */
    private function getUnknown(): string
    {

        return __('Unknown');
    }
}
