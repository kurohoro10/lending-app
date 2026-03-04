<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Twilio ────────────────────────────────────────────────────────
            ['key' => 'twilio_sid',            'value' => null,                          'is_secret' => true],
            ['key' => 'twilio_auth_token',     'value' => null,                          'is_secret' => true],
            ['key' => 'twilio_sms_from',       'value' => null,                          'is_secret' => false],
            ['key' => 'twilio_whatsapp_from',  'value' => null,                          'is_secret' => false],

            // ── Mail / SMTP ───────────────────────────────────────────────────
            ['key' => 'mail_host',             'value' => null,                          'is_secret' => false],
            ['key' => 'mail_port',             'value' => '587',                         'is_secret' => false],
            ['key' => 'mail_username',         'value' => null,                          'is_secret' => false],
            ['key' => 'mail_password',         'value' => null,                          'is_secret' => true],
            ['key' => 'mail_encryption',       'value' => 'tls',                         'is_secret' => false],
            ['key' => 'mail_from_address',     'value' => null,                          'is_secret' => false],
            ['key' => 'mail_from_name',        'value' => null,                          'is_secret' => false],

            // ── Basiq ─────────────────────────────────────────────────────────
            ['key' => 'basiq_env',             'value' => 'sandbox',                     'is_secret' => false],
            ['key' => 'basiq_api_key',         'value' => null,                          'is_secret' => true],
            ['key' => 'basiq_base_url',        'value' => 'https://au-api.basiq.io',     'is_secret' => false],
            ['key' => 'basiq_webhook_secret',  'value' => null,                          'is_secret' => true],

            // ── CreditSense ───────────────────────────────────────────────────
            ['key' => 'creditsense_env',            'value' => 'sandbox',                                 'is_secret' => false],
            ['key' => 'creditsense_client_code',    'value' => null,                                      'is_secret' => false],
            ['key' => 'creditsense_api_key',        'value' => null,                                      'is_secret' => true],
            ['key' => 'creditsense_base_url',       'value' => 'https://au-api.creditsense.com.au',       'is_secret' => false],
            ['key' => 'creditsense_webhook_secret', 'value' => null,                                      'is_secret' => true],
            ['key' => 'creditsense_js_cdn',         'value' => null,                                      'is_secret' => false],

            // ── Bank / Credit Check API ───────────────────────────────────────
            ['key' => 'bank_api_provider_name',    'value' => null,  'is_secret' => false],
            ['key' => 'bank_api_client',           'value' => null,  'is_secret' => false],
            ['key' => 'bank_api_key',              'value' => null,  'is_secret' => true],
            ['key' => 'bank_api_base_url',         'value' => null,  'is_secret' => false],
            ['key' => 'bank_api_webhook_secret',   'value' => null,  'is_secret' => true],
            ['key' => 'bank_api_field_map',        'value' => null,  'is_secret' => false],

            // ── Active bank connection provider ───────────────────────────────
            ['key' => 'active_bank_provider',      'value' => 'basiq', 'is_secret' => false],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'is_secret' => $setting['is_secret']]
            );
        }
    }
}
