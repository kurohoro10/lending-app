<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationUnderReview extends Notification
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
            ->subject('Application Under Review - ' . $this->application->application_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your loan application is now under review by our assessment team.')
            ->line('**Application Number:** ' . $this->application->application_number)
            ->line('**Loan Amount:** $' . number_format($this->application->loan_amount, 2))
            ->line('**Expected Timeline:** We aim to complete our review within 2-3 business days.')
            ->action('View Application', route('applications.show', $this->application))
            ->line('You will receive an update once our assessment is complete.');
    }

    public function toArray($notifiable): array
    {
        return [
            'application_id'     => $this->application->id,
            'application_number' => $this->application->application_number,
            'message'            => 'Your application is now under review.',
            'action_url'         => route('applications.show', $this->application),
        ];
    }
}
