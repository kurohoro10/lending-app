<?php

namespace App\Notifications\Application;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestionAsked extends Notification implements ShouldQueue
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

        $mail = (new MailMessage)
            ->subject('New Question on Your Application - ' . $application->application_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Our team has asked a question regarding your loan application and requires your response.');

        if ($this->question->is_mandatory) {
            $mail->line('⚠️ This question is mandatory and must be answered before your application can proceed.');
        }

        return $mail
            ->line('**Application:** ' . $application->application_number)
            ->line('**Question:** ' . $this->question->question)
            ->action('Answer Question', route('applications.show', $application))
            ->line('Please log in and provide your answer at your earliest convenience.');
    }

    public function toArray($notifiable): array
    {
        $application = $this->question->application;

        return [
            'question_id'        => $this->question->id,
            'application_id'     => $application->id,
            'application_number' => $application->application_number,
            'message'            => 'A new question has been asked on your application.',
            'is_mandatory'       => $this->question->is_mandatory,
            'action_url'         => route('applications.show', $application),
        ];
    }
}
