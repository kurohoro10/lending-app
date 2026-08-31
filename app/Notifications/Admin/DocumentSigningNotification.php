<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentSigningNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Application $application,
        protected string $signedUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appNumber = $this->application->application_number;

        return (new MailMessage)
            ->subject("Action Required: Document Signing — {$appNumber}")
            ->greeting("Dear {$notifiable->name},")
            ->line("Your loan application **{$appNumber}** has a document ready for your review and signature.")
            ->action('Review & Sign Document', $this->signedUrl)
            ->line('Please complete this at your earliest convenience.')
            ->line('If you have any questions, please contact our office.')
            ->salutation('Regards, ' . config('app.name'));
    }
}
