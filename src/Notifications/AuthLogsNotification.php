<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Notifications;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Akira\LaravelAuthLogs\Concerns\InteractsWithLogs;
use Akira\LaravelAuthLogs\Contracts\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

final class AuthLogsNotification extends Notification implements ShouldQueue
{
    use InteractsWithLogs;
    use Queueable;
    use SerializesModels;

    private AuthenticationLog $log;

    private bool $hasDeferredLog;

    /**
     * @phpstan-param Template|string $template
     */
    public function __construct(
        private Template|string $template,
        ?AuthenticationLog $log = null,
    ) {
        $this->log = $log ?? new AuthenticationLog();
        $this->hasDeferredLog = $log instanceof AuthenticationLog;
    }

    /**
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {

        $channels = type($notifiable->notifyAuthenticationLogVia())->asArray(); // @phpstan-ignore-line
        $resolvedChannels = [];

        foreach ($channels as $channel) {
            $channel = type($channel)->asString();

            if ($channel !== 'mail') {
                throw new RuntimeException('Laravel Auth Logs only supports the mail notification channel by default.');
            }

            $resolvedChannels[] = $channel;
        }

        return $resolvedChannels;
    }

    /**
     * @throws RuntimeException
     */
    public function toMail(mixed $notifiable): MailMessage
    {

        return $this->resolveTemplate()->toMail($notifiable);
    }

    /**
     * @throws RuntimeException
     */
    private function resolveTemplate(): Template
    {

        if ($this->template instanceof Template) {
            return $this->template;
        }

        if (! $this->hasDeferredLog) {
            throw new RuntimeException('Authentication log is required to build deferred auth log notification template.');
        }

        $template = app(
            abstract  : $this->template,
            parameters: [
                'loginAt' => $this->getLoginAt(),
                'ipAddress' => $this->getIpAddress(),
                'location' => $this->getFullLocation(),
                'userAgent' => $this->getUserAgent(),
            ],
        );

        if (! $template instanceof Template) {
            throw new RuntimeException('Auth log notification template must implement the template contract.');
        }

        return $template;
    }
}
