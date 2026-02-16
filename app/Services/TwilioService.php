<?php

/**
 * File: app/Services/TwilioService.php
 * Description: Service layer for sending WhatsApp messages via Twilio Sandbox.
 */

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Communication;
use App\Models\Application;

class TwilioService
{
    protected Client $client;
    protected string $whatsappFrom;
    protected string $smsFrom;

    public function __construct()
    {
        $sid = config('twilio.sid');
        $token = config('twilio.auth_token');
        $whatsappFrom = config('twilio.whatsapp_from');
        $smsFrom = config('twilio.sms_from');

        if (!$sid || !$token) {
            throw new \RuntimeException('Twilio credentials are not configured.');
        }

        if (!$smsFrom) {
            throw new \RuntimeException('TWILIO_SMS_FROM is not configured.');
        }

        $this->client = new Client($sid, $token);
        $this->whatsappFrom = $whatsappFrom ?? '';
        $this->smsFrom = $smsFrom;
    }

    /**
     * Send WhatsApp message
     *
     * @param string $to Recipient number (E.164 format e.g. +639XXXXXXXXX)
     * @param string $message
     * @return void
     */
    public function sendWhatsApp(string $to, string $message, ?Application $application = null): array
    {
        try {
            $twilioMessage = $this->client->messages->create(
                "whatsapp:$to",
                [
                    'from' => $this->whatsappFrom,
                    'body' => $message,
                ]
            );

            // Log communication
            if ($application) {
                $this->logCommunication(
                    $application,
                    'whatsapp',
                    'outbound',
                    $to,
                    $message,
                    $twilioMessage->sid,
                    'sent'
                );
            }

            return [
                'success' => true,
                'message_sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
            ];

        } catch (\Exception $e) {
            Log::error('Twilio WhatsApp Error: ' . $e->getMessage());

            if ($application) {
                $this->logCommunication(
                    $application,
                    'whatsapp',
                    'outbound',
                    $to,
                    $message,
                    null,
                    'failed'
                );
            }

            throw $e;
        }
    }

    /**
     * Send SMS message
     */
    public function sendSMS(string $to, string $message, ?Application $application = null): array
    {
        try {
            $twilioMessage = $this->client->messages->create(
                $to, // No 'whatsapp:' prefix for SMS
                [
                    'from' => $this->smsFrom,
                    'body' => $message,
                ]
            );

            // Log communication
            if ($application) {
                $this->logCommunication(
                    $application,
                    'sms',
                    'outbound',
                    $to,
                    $message,
                    $twilioMessage->sid,
                    'sent'
                );
            }

            return [
                'success' => true,
                'message_sid' => $twilioMessage->sid,
                'status' => $twilioMessage->status,
            ];

        } catch (\Exception $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage());

            if ($application) {
                $this->logCommunication(
                    $application,
                    'sms',
                    'outbound',
                    $to,
                    $message,
                    null,
                    'failed'
                );
            }

            throw $e;
        }
    }

    /**
     * Log communication to database
     */
    protected function logCommunication(
        Application $application,
        string $type,
        string $direction,
        string $recipient,
        string $message,
        ?string $externalId,
        string $status
    ): void {
        Communication::create([
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'type' => $type,
            'direction' => $direction,
            'from_address' => $direction === 'outbound'
                ? config('twilio.whatsapp_from')
                : $recipient,
            'to_address' => $direction === 'outbound'
                ? $recipient
                : config('twilio.whatsapp_from'),
            'subject' => null,
            'body' => $message,
            'metadata' => null,
            'status' => $status,
            'sent_at' => now(),
            'delivered_at' => null,
            'read_at' => null,
            'error_message' => null,
            'external_id' => $externalId,
            'sender_ip' => request()->ip(),
        ]);
    }

    /**
     * Handle incoming message (webhook)
     */
    public function handleIncoming(array $data): void
    {
        $from = $data['From'] ?? null;
        $body = $data['Body'] ?? null;
        $messageSid = $data['MessageSid'] ?? null;

        // Determine if WhatsApp or SMS
        $type = str_starts_with($from, 'whatsapp:') ? 'whatsapp' : 'sms';
        $cleanFrom = str_replace('whatsapp:', '', $from);

        // Find application by phone number
        $application = $this->findApplicationByPhone($cleanFrom);

        if ($application) {
            $this->logCommunication(
                $application,
                $type,
                'inbound',
                $cleanFrom,
                $body,
                $messageSid,
                'received'
            );
        }

        Log::info("Incoming {$type} from {$cleanFrom}: {$body}");
    }

    /**
     * Update message status (delivery/read receipts)
     */
    public function updateStatus(string $messageSid, string $status): void
    {
        Communication::where('external_id', $messageSid)
            ->update(['status' => $status]);
    }

    /**
     * Find application by phone number
     */
    protected function findApplicationByPhone(string $phone): ?Application
    {
        return Application::whereHas('personalDetails', function ($query) use ($phone) {
            $query->where('mobile_phone', $phone);
        })->latest()->first();
    }
}
