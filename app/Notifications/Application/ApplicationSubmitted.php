<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmitted extends Notification implements ShouldQueue
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
            ->subject('Application Submitted Successfully - ' . $this->application->application_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for submitting your loan application!')
            ->line('**Application Details:**')
            ->line('Application Number: **' . $this->application->application_number . '**')
            ->line('Loan Amount: **$' . number_format($this->application->loan_amount, 2) . '**')
            ->line('Term: **' . $this->application->term_months . ' months**')
            ->line('Submitted: **' . $this->application->submitted_at->format('d M Y, g:i A') . '**')
            ->line('')
            ->line('**What happens next?**')
            ->line('• Our team will review your application within 24-48 hours')
            ->line('• You will receive an email confirmation shortly')
            ->line('• We may contact you if additional information is needed')
            ->line('• You can track your application status in your dashboard')
            ->action('View Application Status', route('applications.show', $this->application))
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you for choosing us!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'message' => 'Your loan application has been submitted successfully.',
            'action_url' => route('applications.show', $this->application),
            'submitted_at' => $this->application->submitted_at,
        ];
    }
}
