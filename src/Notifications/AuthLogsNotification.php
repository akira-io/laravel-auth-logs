<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Notifications;

use Akira\LaravelAuthLogs\Contracts\ToMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use RuntimeException;

final class AuthLogsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @phpstan-param ToMail $template
     */
    public function __construct(private readonly ToMail $template) {}

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
     * @phpstan-return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {

        return $this->template->toMail($notifiable);
    }
}
