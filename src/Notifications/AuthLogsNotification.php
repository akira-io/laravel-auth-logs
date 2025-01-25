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

    public function __construct(protected Template $template) {}

    public function via($notifiable): array
    {

        return $notifiable->notifyAuthenticationLogVia();
    }

    public function toMail($notifiable): MailMessage
    {

        return $this->template->toMail($notifiable);
    }
}
