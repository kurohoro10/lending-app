<?php

namespace App\Mail\Admin;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdHocEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public string $recipientName,
        public string $emailSubject,
        public string $messageBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
            replyTo: [
                new \Illuminate\Mail\Mailables\Address(
                    'reply-' . $this->application->application_number . '@commercial-loan.endurego.com',
                    config('app.name')
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.ad-hoc',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}