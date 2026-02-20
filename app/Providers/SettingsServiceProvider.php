<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application settings from the database into config().
     *
     * This runs on every request so services like Twilio and SMTP
     * always use the latest saved values without a deploy or .env change.
     */
    public function boot(): void
    {
        // Guard: skip if the settings table doesn't exist yet (e.g. before migrations)
        try {
            if (!Schema::hasTable('settings')) return;
        } catch (\Exception $e) {
            return;
        }

        try {
            $settings = DB::table('settings')->pluck('value', 'key');

            // ── Twilio ───────────────────────────────────────────────────────
            if ($settings->has('twilio_sid')) {
                config(['twilio.sid'           => $settings['twilio_sid']]);
                config(['twilio.auth_token'    => $settings['twilio_auth_token']]);
                config(['twilio.sms_from'      => $settings['twilio_sms_from']]);
                config(['twilio.whatsapp_from' => $settings['twilio_whatsapp_from']]);
            }

            // ── Mail / SMTP ──────────────────────────────────────────────────
            if ($settings->has('mail_host')) {
                config(['mail.mailers.smtp.host'       => $settings['mail_host']]);
                config(['mail.mailers.smtp.port'       => $settings['mail_port']]);
                config(['mail.mailers.smtp.username'   => $settings['mail_username']]);
                config(['mail.mailers.smtp.password'   => $settings['mail_password']]);
                config(['mail.mailers.smtp.encryption' => $settings['mail_encryption']]);
                config(['mail.from.address'            => $settings['mail_from_address']]);
                config(['mail.from.name'               => $settings['mail_from_name']]);
            }

            // ── CreditSense ──────────────────────────────────────────────────
            if ($settings->has('creditsense_api_key')) {
                config(['creditsense.api_key'    => $settings['creditsense_api_key']]);
                config(['creditsense.client'     => $settings['creditsense_client']]);
                config(['creditsense.base_url'   => $settings['creditsense_base_url']]);
            }

        } catch (\Exception $e) {
            Log::error('SettingsServiceProvider failed to load settings: ' . $e->getMessage());
        }
    }
}
