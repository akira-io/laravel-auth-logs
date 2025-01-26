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

    /**
     * Send notification to the user.
     */
    public function __construct(
        private Authenticatable $authenticatable,
        private string $template,
        private AuthenticationLog $log,
    ) {}

    /**
     * Create a new instance of the class.
     */
    public static function make(Authenticatable $authenticatable, string $template, AuthenticationLog $log): self
    {

        return new self($authenticatable, $template, $log);
    }

    /**
     * Send the notification.
     */
    public function send(): void
    {

        $this->validateProperties();

        $notification = $this->buildNotification();

        $this->authenticatable->notify($notification); // @phpstan-ignore-line
    }

    /**
     * Validate the properties.
     */
    private function validateProperties(): void
    {

        if (! isset($this->authenticatable)) {
            throw new RuntimeException('Authenticatable is required');
        }

        if ($this->template === '' || $this->template === '0') {
            throw new RuntimeException('Template is required');
        }

        if (! isset($this->log)) {
            throw new RuntimeException('Authentication log is required');
        }
    }

    /**
     * Build the notification.
     */
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
