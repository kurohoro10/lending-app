<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationDeferred extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application,
        public ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Loan Application Update - ' . $this->application->application_number)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Thank you for submitting your loan application.')
            ->line('After an initial review, we are unable to proceed with your application at this time.');

        if ($this->reason) {
            $mail->line('Reason: ' . $this->reason);
        }

        return $mail
            ->line('Our team may reach out if your circumstances change or if further information is required.')
            ->action('View Application', route('applications.show', $this->application))
            ->line('If you have any questions, please do not hesitate to contact our support team.')
            ->line('Visit us at: ' . config('app.url'));
    }

    public function toArray($notifiable): array
    {
        return [
            'application_id'     => $this->application->id,
            'application_number' => $this->application->application_number,
            'reason'             => $this->reason,
            'message'            => 'Your loan application has been deferred',
        ];
    }
}