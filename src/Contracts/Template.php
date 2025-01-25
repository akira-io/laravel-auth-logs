<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Contracts;

use Akira\LaravelAuthLogs\AuthenticationLog;

interface Template
{
    /**
     * Construct a new template.
     */
    public function __construct(AuthenticationLog $authenticationLog);

    /**
     * Get the login user agent.
     */
    public function getUserAgent(): string;

    /**
     * Get the full location.
     */
    public function getFullLocation(): string;

    /**
     * Get the login date.
     */
    public function getLoginAt(): string;

    /**
     * Get the login IP address.
     */
    public function getIpAddress(): string;
}
