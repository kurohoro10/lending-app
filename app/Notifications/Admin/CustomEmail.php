<?php

namespace App\Notifications\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomEmail extends Notification
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $subject,
        public string $messageBody,
        public ?string $actionText = null,
        public ?string $actionUrl = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

   public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->replyTo(
                // 'reply-' . $this->application->application_number . '@sandbox4b8c4e68e0764885881a2fef961eb55a.mailgun.org',
                'reply-' . $this->application->application_number . '@sandboxfbe5c78007f44292b4c51036ea1e3cb9.mailgun.org',
                config('app.name')
            )
            ->bcc(config('mail.archive_email'))
            ->subject($this->subject)
            ->greeting('Hello!')
            ->line('Dear ' . $notifiable->name . ',');

        foreach (explode("\n", $this->messageBody) as $line) {
            $mail->line($line === '' ? ' ' : $line);
        }

        $mail->line('Application: ' . $this->application->application_number);

        if ($this->actionText && $this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        } else {
            $mail->action('View Application', route('applications.show', $this->application));
        }

        return $mail->line('If you have any questions, please don\'t hesitate to contact us.');
    }
}
