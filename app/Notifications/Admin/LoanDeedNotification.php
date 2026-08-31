<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanDeedNotification extends Notification
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
            ->subject("Action Required: Loan Deed — {$appNumber}")
            ->greeting("Dear {$notifiable->name},")
            ->line("Your loan application **{$appNumber}** has progressed to loan documentation.")
            ->line("Please review and sign the Loan Deed for your facility.")
            ->line("**Loan Amount:** \${$loanAmount}")
            ->action('Review & Sign Loan Deed', $this->signedUrl)
            ->line('Please complete this document at your earliest convenience.')
            ->line('If you have any questions, please contact our office.')
            ->salutation('Regards, ' . config('app.name'));
    }
}
