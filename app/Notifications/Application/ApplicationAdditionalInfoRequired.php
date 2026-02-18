<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationAdditionalInfoRequired extends Notification
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
            ->subject('Additional Information Required - ' . $this->application->application_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We need additional information to proceed with your loan application.')
            ->line('**Application Number:** ' . $this->application->application_number)
            ->line('Please log in to your account to view what information is required and provide the necessary details.')
            ->action('Update Application', route('applications.show', $this->application))
            ->line('If you have any questions, please don\'t hesitate to contact us.');
    }

    public function toArray($notifiable): array
    {
        return [
            'application_id'     => $this->application->id,
            'application_number' => $this->application->application_number,
            'message'            => 'Additional information required for your application.',
            'action_url'         => route('applications.show', $this->application),
        ];
    }
}
