<?php

namespace App\Services;

use App\Models\Communication;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send an email and log the communication
     */
    public function send(
        Application $application,
        string $to,
        string $subject,
        string $body,
        ?string $from = null
    ): Communication {
        $from = $from ?? config('mail.from.address');

        // Create communication record
        $communication = $application->communications()->create([
            'user_id' => $application->user_id,
            'type' => 'email_out',
            'direction' => 'outbound',
            'from_address' => $from,
            'to_address' => $to,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'sender_ip' => request()->ip(),
        ]);

        try {
            // Send email
            Mail::raw($body, function ($message) use ($to, $subject, $from) {
                $message->to($to)
                    ->subject($subject)
                    ->from($from);
            });

            // Update communication status
            $communication->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Log::info("Email sent to {$to}: {$subject}");

        } catch (\Exception $e) {
            // Update communication with error
            $communication->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Failed to send email to {$to}: {$e->getMessage()}");

            throw $e;
        }

        return $communication;
    }

    /**
     * Log an incoming email
     */
    public function logIncoming(
        Application $application,
        string $from,
        string $subject,
        string $body
    ): Communication {
        return $application->communications()->create([
            'user_id' => $application->user_id,
            'type' => 'email_in',
            'direction' => 'inbound',
            'from_address' => $from,
            'to_address' => config('mail.from.address'),
            'subject' => $subject,
            'body' => $body,
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Send application submitted notification
     */
    public function sendApplicationSubmitted(Application $application): void
    {
        $personalDetails = $application->personalDetails;

        $subject = "Application {$application->application_number} Submitted";
        $body = "Dear {$personalDetails->full_name},\n\n";
        $body .= "Thank you for submitting your loan application #{$application->application_number}.\n\n";
        $body .= "Your application is now under review. We will contact you if we require any additional information.\n\n";
        $body .= "Best regards,\n";
        $body .= config('app.name');

        $this->send($application, $personalDetails->email, $subject, $body);
    }

    /**
     * Send document requested notification
     */
    public function sendDocumentRequested(Application $application, string $documentType): void
    {
        $personalDetails = $application->personalDetails;

        $subject = "Document Request - Application {$application->application_number}";
        $body = "Dear {$personalDetails->full_name},\n\n";
        $body .= "We require the following document for your loan application:\n\n";
        $body .= "- {$documentType}\n\n";
        $body .= "Please upload this document through your application portal.\n\n";
        $body .= "Best regards,\n";
        $body .= config('app.name');

        $this->send($application, $personalDetails->email, $subject, $body);
    }

    /**
     * Send status change notification
     */
    public function sendStatusChanged(Application $application, string $oldStatus, string $newStatus): void
    {
        $personalDetails = $application->personalDetails;

        $subject = "Application Status Update - {$application->application_number}";
        $body = "Dear {$personalDetails->full_name},\n\n";
        $body .= "Your loan application status has been updated:\n\n";
        $body .= "Previous Status: " . ucwords(str_replace('_', ' ', $oldStatus)) . "\n";
        $body .= "New Status: " . ucwords(str_replace('_', ' ', $newStatus)) . "\n\n";
        $body .= "Please log in to your account to view more details.\n\n";
        $body .= "Best regards,\n";
        $body .= config('app.name');

        $this->send($application, $personalDetails->email, $subject, $body);
    }
}
