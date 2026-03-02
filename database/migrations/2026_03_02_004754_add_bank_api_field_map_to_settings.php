<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Add a dedicated row for the bank API field map.
     *
     * Rather than a schema column, the field map is stored as a normal
     * settings row (key = 'bank_api_field_map', value = JSON string).
     * This migration seeds the row so it exists with a sensible default
     * and documents the expected JSON shape.
     *
     * Default map keys are the normalised field names the application
     * code reads internally; values are the dotted-path keys from the
     * provider's actual API response payload.
     */
    public function up(): void
    {
        // Seed the field-map setting with a documented default.
        // Admins override this via the Settings UI textarea.
        Setting::firstOrCreate(
            ['key' => 'bank_api_field_map'],
            [
                'value' => json_encode([
                    // Internal field          => Provider response path
                    'applicant_name'           => 'applicant.fullName',
                    'account_number'           => 'bankAccount.accountNumber',
                    'bsb'                      => 'bankAccount.bsb',
                    'balance'                  => 'summary.currentBalance',
                    'income_monthly'           => 'income.monthlyAverage',
                    'expenses_monthly'         => 'expenses.monthlyAverage',
                    'gambling_flag'            => 'flags.gambling',
                    'dishonour_count_90d'      => 'flags.dishonourCount90Days',
                ], JSON_PRETTY_PRINT),
                'is_secret' => false,
            ]
        );

        // Remove legacy CreditSense-specific settings rows if they exist
        // (safe to run even if the rows were never seeded).
        Setting::whereIn('key', [
            'creditsense_client',
            'creditsense_api_key',
            'creditsense_base_url',
        ])->delete();
    }

    public function down(): void
    {
        Setting::where('key', 'bank_api_field_map')->delete();
    }
};
