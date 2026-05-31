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

        $apiBase = config('auth-logs.geolocation_api');

        if (! is_string($apiBase) || $apiBase === '') {
            return collect(self::EMPTY_COLLECTION);
        }

        $scheme = self::schemeFrom($apiBase);

        if ($scheme === 'file') {
            $apiEndpoint = mb_rtrim($apiBase, '/').'/'.$ip;
        } elseif ($scheme === 'data' || $scheme === 'php') {
            // Some stream wrappers are content-only; appending an IP corrupts the payload
            $apiEndpoint = $apiBase;
        } else {
            $apiEndpoint = mb_rtrim($apiBase, '/').'/'.$ip;
        }

        $data = self::fetchGeolocationData($apiEndpoint);

        // Normalize the decoded payload according to scheme and status and
        // produce a single return to make paths explicit and testable.
        $decoded = null;

        if (str_starts_with($apiBase, 'file://')) {
            $decoded = $data ?? (object) self::EMPTY_COLLECTION;
        } else {
            // @phpstan-ignore-next-line
            $decoded = ($data === null || $data->status !== 'success')
                ? (object) self::EMPTY_COLLECTION
                : $data;
        }

        return collect((array) $decoded);
    }

    /**
     * Fetch the geolocation data from the given URL.
     */
    private static function fetchGeolocationData(string $url): mixed
    {

        // Allow file:// scheme for deterministic, offline tests
        if (str_starts_with($url, 'file://')) {
            $path = mb_substr($url, 7);
            if (! is_file($path)) {
                return null;
            }

            return json_decode((string) file_get_contents($path));
        }

        $stream = @fopen($url, 'r');

        if (! is_resource($stream)) {
            return null;
        }

        fclose($stream);

        return json_decode(file_get_contents($url)) ?? null; // @phpstan-ignore-line
    }

    /**
     * Lightweight URL helper: infer scheme without parse_url.
     */
    private static function schemeFrom(string $base): string
    {

        if (str_starts_with($base, 'file://')) {
            return 'file';
        }
        if (str_starts_with($base, 'data://')) {
            return 'data';
        }
        if (str_starts_with($base, 'php://')) {
            return 'php';
        }

        $pos = mb_strpos($base, '://');

        return $pos === false ? '' : mb_substr($base, 0, $pos);
    }
}
