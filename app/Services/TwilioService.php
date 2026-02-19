<?php

/**
 * File: app/Services/TwilioService.php
 * Description: Service layer for sending WhatsApp and SMS via Twilio.
 *              Automatically disabled outside production.
 */

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Communication;
use App\Models\Application;

class TwilioService
{
    protected ?Client $client = null;
    protected ?string $whatsappFrom = null;
    protected ?string $smsFrom = null;
    protected bool $enabled = false;

    public function __construct()
    {
        if (!app()->environment('production')) {
            Log::info('Twilio disabled (non-production environment).');
            return;
        }

        $sid   = config('twilio.sid');
        $token = config('twilio.auth_token');

        $this->whatsappFrom = config('twilio.whatsapp_from');
        $this->smsFrom      = config('twilio.sms_from');

        if (!$sid || !$token || !$this->smsFrom) {
            Log::warning('Twilio credentials incomplete. Service disabled.');
            return;
        }

        $this->client  = new Client($sid, $token);
        $this->enabled = true;
    }

    /**
     * Send SMS message
     */
    public function sendSMS(string $to, string $message, ?Application $application = null): array
    {
        if (!$this->enabled || !$this->client) {
            Log::info('SMS skipped — Twilio disabled.', ['to' => $to]);

            return [
                'success' => false,
                'message' => 'Twilio disabled',
            ];
        }

        try {
            $twilioMessage = $this->client->messages->create(
                $to,
                [
                    'from' => $this->smsFrom,
                    'body' => $message,
                ]
            );

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
                'success'     => true,
                'message_sid' => $twilioMessage->sid,
                'status'      => $twilioMessage->status,
            ];

        } catch (\Exception $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Send WhatsApp message
     *
     * @param string $to Recipient number (E.164 format e.g. +639XXXXXXXXX)
     */
    public function sendWhatsApp(string $to, string $message, ?Application $application = null): array
    {
        // Added the same disabled guard that sendSMS has.
        if (!$this->enabled || !$this->client) {
            Log::info('WhatsApp skipped — Twilio disabled.', ['to' => $to]);

            return [
                'success' => false,
                'message' => 'Twilio disabled',
            ];
        }

        try {
            $twilioMessage = $this->client->messages->create(
                "whatsapp:$to",
                [
                    'from' => $this->whatsappFrom,
                    'body' => $message,
                ]
            );

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
                'success'     => true,
                'message_sid' => $twilioMessage->sid,
                'status'      => $twilioMessage->status,
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
        // ✅ FIX 2: Use the correct from number based on message type.
        // Previously always used whatsapp_from even for SMS messages.
        $ownNumber = $type === 'sms' ? $this->smsFrom : $this->whatsappFrom;

        Communication::create([
            'application_id' => $application->id,
            'user_id'        => $application->user_id,
            'type'           => $type,
            'direction'      => $direction,
            'from_address'   => $direction === 'outbound' ? $ownNumber : $recipient,
            'to_address'     => $direction === 'outbound' ? $recipient  : $ownNumber,
            'subject'        => null,
            'body'           => $message,
            'metadata'       => null,
            'status'         => $status,
            'sent_at'        => now(),
            'delivered_at'   => null,
            'read_at'        => null,
            'error_message'  => null,
            'external_id'    => $externalId,
            'sender_ip'      => request()->ip(),
        ]);
    }

    /**
     * Handle incoming message (webhook)
     */
    public function handleIncoming(array $data): void
    {
        $from       = $data['From']       ?? null;
        $body       = $data['Body']       ?? null;
        $messageSid = $data['MessageSid'] ?? null;

        $type      = str_starts_with($from, 'whatsapp:') ? 'whatsapp' : 'sms';
        $cleanFrom = str_replace('whatsapp:', '', $from);

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
