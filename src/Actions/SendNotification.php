<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Concerns\InteractsWithLogs;
use Akira\LaravelAuthLogs\Contracts\ToMail;
use Akira\LaravelAuthLogs\Notifications\AuthLogsNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use RuntimeException;

final readonly class SendNotification
{
    use InteractsWithLogs;

    /**
     * @phpstan-param string $template
     */
    public function __construct(
        private Authenticatable $authenticatable,
        private string $template,
        private AuthenticationLog $log,
    ) {}

    /**
     * @phpstan-param string $template
     */
    public static function make(Authenticatable $authenticatable, string $template, AuthenticationLog $log): self
    {

        return new self($authenticatable, $template, $log);
    }

    /**
     * @throws RuntimeException
     */
    public function send(): void
    {

        $this->validateProperties();

        /** @phpstan-ignore-next-line */
        $this->authenticatable->notify(new AuthLogsNotification(
            template: $this->template,
            log     : $this->log,
        ));
    }

    /**
     * @throws RuntimeException
     */
    private function validateProperties(): void
    {

        if (! isset($this->authenticatable)) {
            throw new RuntimeException('Authenticatable is required'); // @codeCoverageIgnoreLine
        }

        if ($this->template === '' || $this->template === '0') {
            throw new RuntimeException('Template is required');
        }

        if (! isset($this->log)) {
            throw new RuntimeException('Authentication log is required'); // @codeCoverageIgnoreLine
        }

        if (! is_a($this->template, ToMail::class, true)) {
            throw new RuntimeException('Auth log notification template must implement the mail template contract.');
        }
    }
}
