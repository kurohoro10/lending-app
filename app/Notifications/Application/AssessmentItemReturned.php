<?php

namespace App\Notifications\Application;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an admin returns a single assessment checklist item to the
 * client for correction. Deliberately per-item (not batched) — mirrors
 * QuestionAnswered's granularity, not the bulk "ask" email.
 */
class AssessmentItemReturned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Question $question
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $application = $this->question->application;

        return (new MailMessage)
            ->replyTo(
                'reply-' . $application->application_number . '@commercial-loan.endurego.com',
                config('app.name')
            )
            ->subject('Action Required - Application ' . $application->application_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('One of the items you submitted for your loan application needs to be corrected before we can continue processing it.')
            ->line('**Application:** ' . $application->application_number)
            ->line('**Item:** ' . $this->question->question)
            ->line('**Reason:** ' . $this->question->review_notes)
            ->action('Open Client Portal', route('applications.show', $application))
            ->line('Please log in, review the note above, and resubmit this item.')
            ->line('If you have any questions please contact us on 1300 680 477.');
    }

    public function toArray($notifiable): array
    {
        $application = $this->question->application;

        return [
            'question_id'        => $this->question->id,
            'application_id'     => $application->id,
            'application_number' => $application->application_number,
            'message'            => 'An assessment item was returned for correction: ' . $this->question->question,
            'action_url'         => route('applications.show', $application),
        ];
    }
}
