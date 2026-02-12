<?php

namespace App\Notifications\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;
    protected $changes;

    public function __construct(Application $application, array $changes = [])
    {
        $this->application = $application;
        $this->changes = $changes;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Application Updated - ' . $this->application->application_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your loan application has been updated.')
            ->line('Application Number: **' . $this->application->application_number . '**');

        // Add specific changes if provided
        if (!empty($this->changes)) {
            $message->line('**Changes made:**');
            foreach ($this->changes as $field => $value) {
                $fieldName = ucwords(str_replace('_', ' ', $field));
                if ($field === 'loan_amount') {
                    $message->line('- ' . $fieldName . ': $' . number_format($value, 2));
                } else {
                    $message->line('- ' . $fieldName . ': ' . $value);
                }
            }
        }

        $message->action('View Application', route('applications.edit', $this->application))
            ->line('Thank you for keeping your information up to date!');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'message' => 'Your loan application has been updated.',
            'changes' => $this->changes,
            'action_url' => route('applications.edit', $this->application),
        ];
    }
}
