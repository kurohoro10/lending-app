<?php

namespace App\Notifications\NewUser;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewUser extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to Our Loan Management System!')
            ->greeting('Welcome ' . $notifiable->name . '!')
            ->line('Thank you for registering with us! Your account has been successfully created.')
            ->line('We\'re excited to help you with your loan application journey.')
            ->line('')
            ->line('**Your Application:**')
            ->line('Application Number: **' . $this->application->application_number . '**')
            ->line('Loan Amount: **$' . number_format($this->application->loan_amount, 2) . '**')
            ->line('')
            ->line('**Next Steps:**')
            ->line('1. Complete your personal details')
            ->line('2. Add your residential address history')
            ->line('3. Provide employment information')
            ->line('4. Add your living expenses')
            ->line('5. Upload required documents')
            ->line('6. Submit your application for review')
            ->action('Complete Your Application', route('applications.edit', $this->application))
            ->line('')
            ->line('**Need Help?**')
            ->line('If you have any questions or need assistance, our support team is here to help.')
            ->line('Feel free to contact us at any time.')
            ->line('')
            ->line('Thank you for choosing us!');
    }
}
