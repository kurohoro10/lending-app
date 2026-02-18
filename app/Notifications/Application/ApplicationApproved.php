<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationApproved extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Application Approved - ' . $this->application->application_number)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('We are pleased to inform you that your loan application has been **APPROVED**!')
            ->line('**Application Number:** ' . $this->application->application_number)
            ->line('**Approved Amount:** $' . number_format($this->application->loan_amount, 2))
            ->line('**Term:** ' . $this->application->term_months . ' months')
            ->line('### Next Steps:')
            ->line('1. Review your loan agreement documents')
            ->line('2. Sign and return the required paperwork')
            ->line('3. Funds will be disbursed upon completion')
            ->action('View Application', route('applications.show', $this->application))
            ->line('Our team will contact you shortly to finalize the details.')
            ->line('Thank you for choosing us for your financing needs!');
    }

    public function toArray($notifiable): array
    {
        return [
            'application_id'     => $this->application->id,
            'application_number' => $this->application->application_number,
            'message'            => 'Congratulations! Your application has been approved.',
            'action_url'         => route('applications.show', $this->application),
        ];
    }
}
