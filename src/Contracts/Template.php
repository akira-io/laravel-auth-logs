<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Contracts;

interface Template
{
    /**
     * @phpstan-param string $loginAt
     * @phpstan-param string $ipAddress
     * @phpstan-param string $location
     * @phpstan-param string $userAgent
     */
    public function __construct(
        string $loginAt,
        string $ipAddress,
        string $location,
        string $userAgent,
    );
}
