<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Concerns\InteractWithLogs;
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use RuntimeException;

final readonly class SendNotification
{
    use InteractWithLogs;

    public function __construct(
        private Authenticatable $authenticatable,
        private string $template,
        private AuthenticationLog $log,
    ) {}

    public static function make(Authenticatable $authenticatable, string $template, AuthenticationLog $log): self
    {

        return new self($authenticatable, $template, $log);
    }

    public function send(): void
    {

        $this->validateProperties();

        $notification = $this->buildNotification();

        $this->authenticatable->notify($notification);
    }

    private function validateProperties(): void
    {

        if (! $this->authenticatable) {
            throw new RuntimeException('Authenticatable is required');
        }

        if (! $this->template) {
            throw new RuntimeException('Template is required');
        }

        if (! $this->log) {
            throw new RuntimeException('Authentication log is required');
        }
    }

    private function buildNotification(): AuthLogsNotification
    {

        return new AuthLogsNotification(
            template: app(
                abstract  : $this->template,
                parameters: [
                    'loginAt' => $this->getLoginAt(),
                    'ipAddress' => $this->getIpAddress(),
                    'location' => $this->getFullLocation(),
                    'userAgent' => $this->getUserAgent(),
                ],
            ),
        );
    }
}
