<?php

namespace App\Notifications\TwoFactor;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent synchronously (no ShouldQueue) so the login/challenge request that
 * triggers it is guaranteed the code has actually been dispatched, rather
 * than depending on a queue worker being alive at that moment.
 */
class EmailOtpCode extends Notification
{
    public function __construct(public readonly string $code, public readonly int $expiresInMinutes)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your login verification code')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Use the following code to complete your login:')
            ->line('## ' . $this->code)
            ->line("This code will expire in {$this->expiresInMinutes} minutes.")
            ->line('If you did not attempt to log in, you can safely ignore this email.');
    }
}
