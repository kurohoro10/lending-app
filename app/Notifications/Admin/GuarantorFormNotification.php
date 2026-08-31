<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GuarantorFormNotification extends Notification
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
        $appNumber  = $this->application->application_number;
        $loanAmount = number_format($this->application->loan_amount, 2);

        return (new MailMessage)
            ->subject("Action Required: Guarantor Form — {$appNumber}")
            ->greeting("Dear {$notifiable->name},")
            ->line("Your loan application **{$appNumber}** has been conditionally approved.")
            ->line("As part of the approval process, we require you to review and sign the Guarantor Application, Privacy Consent and Declaration form.")
            ->line("**Loan Amount:** \${$loanAmount}")
            ->action('Review & Sign Guarantor Form', $this->signedUrl)
            ->line('Please complete this form at your earliest convenience.')
            ->line('If you have any questions, please contact our office.')
            ->salutation('Regards, ' . config('app.name'));
    }
}