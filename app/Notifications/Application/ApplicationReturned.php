<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationReturned extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action Required - Application ' . $this->application->application_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your loan application requires some amendments before it can proceed.')
            ->line('**Application:** ' . $this->application->application_number)
            ->line('**Reason:** ' . $this->application->return_reason)
            ->action('Update Your Application', route('applications.edit', $this->application))
            ->line('Please log in, make the necessary changes, and resubmit your application.')
            ->line('If you have any questions, please contact our support team.');
    }

    public function toArray($notifiable): array
    {
        return [
            'application_id'     => $this->application->id,
            'application_number' => $this->application->application_number,
            'message'            => 'Your application requires amendments: ' . $this->application->return_reason,
            'action_url'         => route('applications.edit', $this->application),
        ];
    }
}
