<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated replacement for the four individual migrations below.
 * Delete these files before running:
 *
 *   - xxxx_xx_xx_add_credit_sense_completed_at_to_applications_table.php
 *   - xxxx_xx_xx_add_credit_sense_report_columns_to_applications_table.php
 *   - xxxx_xx_xx_add_verified_expenses_to_applications_table.php
 *   - (any other credit_sense / bank_api partial migrations)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Name of the provider used for this application's bank check
            // (e.g. "CreditSense", "Illion"). Null until a check is initiated.
            $table->string('bank_api_provider_name')->nullable()->after('submission_ip');

            $table->string('bank_api_user_ref')->nullable()->after('bank_api_provider_name');

            // Null = applicant has not yet completed the bank statement flow.
            // Populated when the provider JS callback fires on the front-end.
            $table->timestamp('bank_api_completed_at')->nullable()->after('bank_api_user_ref');

            // Raw JSON report payload received from the provider via webhook.
            $table->json('bank_api_report')->nullable()->after('bank_api_completed_at');

            // When the webhook report was received and stored.
            $table->timestamp('bank_api_report_received_at')->nullable()->after('bank_api_report');

            // Normalised expense breakdown derived from the bank report,
            // keyed by the admin-configured field map.
            $table->json('verified_expenses')->nullable()->after('bank_api_report_received_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'bank_api_provider_name',
                'bank_api_completed_at',
                'bank_api_report',
                'bank_api_report_received_at',
                'verified_expenses',
            ]);
        });
    }
};
