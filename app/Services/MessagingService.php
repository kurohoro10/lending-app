<?php

namespace App\Services;

use App\Models\Application;

/**
 * A thin wrapper around TwilioService so you only need to change
 * ONE line when you switch from WhatsApp (testing) to SMS (production).
 *
 * Inject this class everywhere instead of using TwilioService directly.
 */
class MessagingService
{
    public function __construct(private TwilioService $twilio) {}

    /**
     * @return array{success: bool, message_sid?: string, status?: string, message?: string, error?: string}
     *   TwilioService::sendSMS()'s result, passed through unchanged. Widened
     *   from void so callers that need the message SID / initial delivery
     *   status (e.g. for the activity log) can read it — every existing
     *   caller already discards the return value, so this is additive.
     */
    public function send(string $phone, string $message, ?Application $application = null): array
    {
        // ---------------------------------------------------------------
        // TESTING:  using WhatsApp
        // PRODUCTION: swap to the sendSMS line below and delete this one
        // ---------------------------------------------------------------
        // return $this->twilio->sendWhatsApp($phone, $message, $application);
        return $this->twilio->sendSMS($phone, $message, $application);
    }
}
