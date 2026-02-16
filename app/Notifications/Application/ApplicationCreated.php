<?php

namespace App\Notifications\Application;

use App\Jobs\SendSMSMessage;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Application Started - Complete Your Details')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your loan application has been successfully created.')
            ->line('Application Number: **' . $this->application->application_number . '**')
            ->line('Loan Amount: **$' . number_format($this->application->loan_amount, 2) . '**')
            ->line('Term: **' . $this->application->term_months . ' months**')
            ->action('Complete Your Application', route('applications.edit', $this->application))
            ->line('Please complete all required sections to submit your application for review.')
            ->line('If you have any questions, please don\'t hesitate to contact our support team.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'message' => 'Your loan application has been created. Please complete all sections.',
            'action_url' => route('applications.edit', $this->application),
        ];
    }
}
