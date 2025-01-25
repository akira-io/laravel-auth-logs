<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Contracts;

use Illuminate\Notifications\Messages\MailMessage;

interface ToMail extends Template
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage;
}
