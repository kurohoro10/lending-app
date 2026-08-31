<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DirectorBankConnectionEmail extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $directorName,
        public string $signedUrl,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bank Statement Connection Required')
            ->greeting('Hello!')
            ->line('Dear ' . $this->directorName . ',')
            ->line('We require you to fill in your living expenses as part of the loan application process.')
            ->line('Application: ' . $this->application->application_number)
            ->action('Fill In Living Expenses', $this->signedUrl)
            ->line('If you have any questions, please don\'t hesitate to contact us.');
    }
}