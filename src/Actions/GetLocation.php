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

        $apiBase = type(config('auth-logs.geolocation_api'))->asString();
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

        if (str_starts_with(type(config('auth-logs.geolocation_api'))->asString(), 'file://')) {
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

        $contents = @file_get_contents($url, false, self::streamContext());

        if ($contents === false) {
            return null;
        }

        return json_decode($contents) ?? null;
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

    /**
     * @return resource
     */
    private static function streamContext(): mixed
    {

        return stream_context_create([
            'http' => ['timeout' => 5],
            'https' => ['timeout' => 5],
        ]);
    }
}
