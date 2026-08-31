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
            ->replyTo(
                'reply-' . $this->application->application_number . '@commercial-loan.endurego.com',
                config('app.name')
            )
            ->subject('Application Submitted - ' . $this->application->application_number)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Thank you for your application.')
            ->line('As part of our identity verification process and to comply with our regulatory obligations, we kindly ask that you provide the following documents by replying directly to this email:')
            ->line('**1.** A clear colour copy of your current Australian Driver Licence or Passport.')
            ->line('**2.** A selfie of yourself holding the same identification document beside your face, ensuring both your face and the identification details are clearly visible.')
            ->line('**3.** A second selfie of yourself holding:')
            ->line('&nbsp;&nbsp;&nbsp;&nbsp;• the same identification document; and')
            ->line('&nbsp;&nbsp;&nbsp;&nbsp;• a handwritten piece of paper showing today\'s date (DD/MM/YYYY).')
            ->line('Please ensure all photographs are:')
            ->line('• Clear and in focus.')
            ->line('• Taken in good lighting.')
            ->line('• Show the entire identification document without any details being obscured.')
            ->line('• Not edited or filtered.')
            ->line('Simply reply to this email and attach the requested images. Once we receive your documents, we will review them promptly and contact you if any further information is required.')
            ->line('If you have any questions or require assistance, please do not hesitate to contact us.')
            ->line('Visit us at: ' . config('app.url'))
            ->salutation('Kind regards, ' . config('app.name'));
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