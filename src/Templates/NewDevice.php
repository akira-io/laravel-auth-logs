<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Templates;

use Akira\LaravelAuthLogs\Contracts\ToMail;
use Illuminate\Notifications\Messages\MailMessage;

final readonly class NewDevice implements ToMail
{
    public function __construct(
        private string $loginAt,
        private string $ipAddress,
        private string $location,
        private string $userAgent,
    ) {}

    public function toMail(mixed $notifiable): MailMessage
    {

        return (new MailMessage)
            ->subject(__('New device login'))
            ->greeting(__('Hello!'))
            ->line(__('A new device has logged into your account.'))
            ->line(__('If this was you, you can safely ignore this email.'))
            ->line(__('If you suspect that someone else tried to access your account, please contact us immediately.'))
            ->line(__('Login details:'))
            ->line(__('Account: :account', ['account' => $notifiable->email]))
            ->line(__('Date: :date', ['date' => $this->loginAt]))
            ->line(__('IP address: :ip', ['ip' => $this->ipAddress]))
            ->line(__('User agent: :user_agent', ['user_agent' => $this->userAgent]))
            ->line(__('Location: :location', ['location' => $this->location]))
            ->line(__('Thank you for using our application!'));
    }
}
