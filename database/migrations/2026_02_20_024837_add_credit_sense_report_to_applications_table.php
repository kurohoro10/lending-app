<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Stores the raw CreditSense webhook report payload
            $table->json('credit_sense_report')->nullable()->after('credit_sense_completed_at');
            // When the report was received via webhook
            $table->timestamp('credit_sense_report_received_at')->nullable()->after('credit_sense_report');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['credit_sense_report', 'credit_sense_report_received_at']);
        });
    }
};
