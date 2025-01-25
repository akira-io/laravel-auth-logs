<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Templates;

use Akira\LaravelAuthLogs\Concerns\InteractWithLogs;
use Akira\LaravelAuthLogs\Contracts\ToMail;
use Illuminate\Notifications\Messages\MailMessage;

final readonly class FailedLogin implements ToMail
{
    use InteractWithLogs;

    public function toMail(mixed $notifiable): MailMessage
    {

        return (new MailMessage)
            ->subject(__('Failed login attempt'))
            ->greeting(__('Hello!'))
            ->line(__('We detected a failed login attempt on your account.'))
            ->line(__('If this was you, you can safely ignore this email.'))
            ->line(__('If you suspect that someone else tried to access your account, please contact us immediately.'))
            ->line(__('Login details:'))
            ->line(__('Account: :account', ['account' => $notifiable->email]))
            ->line(__('Date: :date', ['date' => $this->getLoginAt()]))
            ->line(__('IP address: :ip', ['ip' => $this->getIpAddress()]))
            ->line(__('User agent: :user_agent', ['user_agent' => $this->getUserAgent()]))
            ->line(__('Location: :location', ['location' => $this->getFullLocation()]))
            ->line(__('Thank you for using our application!'));
    }
}
