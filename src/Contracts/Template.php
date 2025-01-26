<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Contracts;

interface Template
{
    public function __construct(
        string $loginAt,
        string $ipAddress,
        string $location,
        string $userAgent,
    );
}
