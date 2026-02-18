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

    public function send(string $phone, string $message, ?Application $application = null): void
    {
        // ---------------------------------------------------------------
        // TESTING:  using WhatsApp
        // PRODUCTION: swap to the sendSMS line below and delete this one
        // ---------------------------------------------------------------
        // $this->twilio->sendWhatsApp($phone, $message, $application);
        $this->twilio->sendSMS($phone, $message, $application);
    }
}
