<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Contracts;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * @method MailMessage toMail(mixed $notifiable)
 *                                               /
 */
interface Template
{
    /**
     * Get the mail representation of the notification.
     */
    public function __construct(
        string $loginAt,
        string $ipAddress,
        string $location,
        string $userAgent,
    );
}
