<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Illuminate\Support\Collection;

final class GetLocation
{
    private const array EMPTY_COLLECTION = [];

    public static function make(string $ip): Collection
    {

        $apiEndpoint = config('auth-logs.geolocation_api').'/'.$ip;

        $data = self::fetchGeolocationData($apiEndpoint);

        if ($data === null || $data->status !== 'success') {
            return collect(self::EMPTY_COLLECTION);
        }

        return collect((array) $data);
    }

    private static function fetchGeolocationData(string $url): ?object
    {

        $stream = @fopen($url, 'r');

        if (! is_resource($stream)) {
            return null;
        }

        fclose($stream);

        return json_decode(file_get_contents($url)) ?? null;
    }
}
