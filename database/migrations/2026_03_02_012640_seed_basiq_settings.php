<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any Plaid rows from the earlier iteration
        Setting::whereIn('key', ['plaid_env', 'plaid_client_id', 'plaid_secret'])->delete();

        // Seed Basiq rows — values left blank for the admin to fill in via UI
        $defaults = [
            ['key' => 'basiq_env',      'value' => 'sandbox',                  'is_secret' => false],
            ['key' => 'basiq_api_key',  'value' => '',                         'is_secret' => true],
            ['key' => 'basiq_base_url', 'value' => 'https://au-api.basiq.io',  'is_secret' => false],
        ];

        foreach ($defaults as $row) {
            Setting::firstOrCreate(
                ['key' => $row['key']],
                ['value' => $row['value'], 'is_secret' => $row['is_secret']]
            );
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', ['basiq_env', 'basiq_api_key', 'basiq_base_url'])->delete();
    }
};
