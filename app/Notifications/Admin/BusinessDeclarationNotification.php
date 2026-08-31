<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessDeclarationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $signedUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Business Purpose Declaration — {$this->application->application_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Please review and sign the Business Purpose Declaration for your loan application **{$this->application->application_number}**.")
            ->line("This declaration confirms that the loan funds will be used wholly or predominantly for business or investment purposes.")
            ->action('Sign Declaration', $this->signedUrl)
            ->line("This link will expire. If you have any questions, please contact us.")
            ->salutation('Regards, AHA Money');
    }
}