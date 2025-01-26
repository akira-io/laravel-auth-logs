<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Illuminate\Support\Collection;

/**
 * Get the location of the given IP address.
 */
final class GetLocation
{
    private const array EMPTY_COLLECTION = [];

    /**
     * Get the location of the given IP address.
     *
     * @return Collection<string, mixed>
     */
    public static function make(string $ip): Collection
    {

        $apiEndpoint = config('auth-logs.geolocation_api').'/'.$ip;

        $data = self::fetchGeolocationData($apiEndpoint);
        // @phpstan-ignore-next-line
        if ($data === null || $data->status !== 'success') {
            return collect(self::EMPTY_COLLECTION);
        }

        return collect((array) $data);
    }

    /**
     * Fetch the geolocation data from the given URL.
     */
    private static function fetchGeolocationData(string $url): mixed
    {

        $stream = @fopen($url, 'r');

        if (! is_resource($stream)) {
            return null;
        }

        fclose($stream);

        return json_decode(file_get_contents($url)) ?? null; // @phpstan-ignore-line
    }
}
