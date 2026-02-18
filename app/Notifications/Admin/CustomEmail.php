<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomEmail extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $subject,
        public string $messageBody
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello!')
            ->line($this->messageBody)
            ->line('Application: ' . $this->application->application_number)
            ->action('View Application', route('applications.show', $this->application))
            ->line('If you have any questions, please don\'t hesitate to contact us.');
    }
}
