<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Notifications;

use Akira\LaravelAuthLogs\Contracts\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AuthLogsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly Template $template) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {

        return $notifiable->notifyAuthenticationLogVia(); // @phpstan-ignore-line
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {

        return $this->template->toMail($notifiable);
    }
}
