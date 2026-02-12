<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationDeclined extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $reason
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Loan Application Status - ' . $this->application->application_number)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Thank you for your loan application.')
            ->line('Unfortunately, we are unable to approve your application at this time.')
            ->line('Reason: ' . $this->reason)
            ->line('You may reapply once you meet our lending criteria.')
            ->action('View Application', route('applications.show', $this->application))
            ->line('If you have any questions, please contact our support team.');
    }

    public function toArray($notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'reason' => $this->reason,
            'message' => 'Your loan application has been declined',
        ];
    }
}
