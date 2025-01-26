<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\ValueObjects;

use Illuminate\Support\Collection;

final readonly class Location
{
    public string $city;

    public string $country;

    public string $timezone;

    public string $latitude;

    public string $longitude;

    public string $isoCode;

    /**
     * Construct a new location.
     *
     * @param  Collection<string,string>  $data
     */
    public function __construct(Collection $data)
    {

        $this->isoCode = (string) $data->get('iso_code');
        $this->country = (string) $data->get('country');
        $this->city = (string) $data->get('city');
        $this->latitude = (string) $data->get('lat');
        $this->longitude = (string) $data->get('lon');
        $this->timezone = (string) $data->get('timezone');
    }

    /**
     * Make a new location.
     *
     * @param  Collection<string,mixed>  $data
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
