<?php

namespace App\Notifications\Admin;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class QuestionAnswered extends Notification
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
            ->subject('Question Answered - Application ' . $application->application_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A client has answered your question.')
            ->line('Application: ' . $application->application_number)
            ->line('Question: ' . $this->question->question)
            ->line('Answer: ' . $this->question->answer)
            ->action('View Application', route('admin.applications.show', $application))
            ->line('Review the answer and continue with the assessment.');
    }

    public function toArray($notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'application_id' => $this->question->application_id,
            'application_number' => $this->question->application->application_number,
            'message' => 'Client answered a question',
        ];
    }
}
