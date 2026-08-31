<?php

namespace App\Notifications\Admin;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskEmailNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Task $task,
        private string $responseUrl
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $clientName = $notifiable->first_name ?? $notifiable->name;

        return (new MailMessage)
            ->subject('Task for You: ' . $this->task->title)
            ->greeting('Hi ' . $clientName . ',')
            ->line('There is a task for you to complete.')
            ->line('**Title:** ' . $this->task->title)
            ->when($this->task->description, fn($mail) =>
                $mail->line('**Description:** ' . $this->task->description)
            )
            // ── Add more task-specific content here in future ──
            ->action('Click Here to Respond', $this->responseUrl)
            ->line('Please respond at your earliest convenience.')
            ->salutation('Thank you, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
