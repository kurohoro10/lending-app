<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationSubmittedAdmin extends Notification implements ShouldQueue
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
        $applicant = $this->application->user;

        return (new MailMessage)
            ->subject('🔔 New Application Submitted - ' . $this->application->application_number)
            ->greeting('Hello Admin!')
            ->line('A new loan application has been submitted and requires review.')
            ->line('**Application Details:**')
            ->line('Application Number: **' . $this->application->application_number . '**')
            ->line('Applicant: **' . $applicant->name . '**')
            ->line('Email: **' . $applicant->email . '**')
            ->line('Loan Amount: **$' . number_format($this->application->loan_amount, 2) . '**')
            ->line('Term: **' . $this->application->term_months . ' months**')
            ->line('Purpose: **' . ucfirst(str_replace('_', ' ', $this->application->loan_purpose)) . '**')
            ->line('Submitted: **' . $this->application->submitted_at->format('d M Y, g:i A') . '**')
            ->action('Review Application', route('applications.show', $this->application))
            ->line('Please review this application at your earliest convenience.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'applicant_name' => $this->application->user->name,
            'loan_amount' => $this->application->loan_amount,
            'message' => 'New application submitted for review.',
            'action_url' => route('applications.show', $this->application),
        ];
    }
}
