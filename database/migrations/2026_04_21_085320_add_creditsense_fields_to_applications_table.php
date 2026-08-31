<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('credit_sense_app_id')->nullable()->after('bank_api_report_received_at');
            $table->timestamp('credit_sense_completed_at')->nullable()->after('credit_sense_app_id');
            $table->json('credit_sense_report')->nullable()->after('credit_sense_completed_at');
            $table->timestamp('credit_sense_report_received_at')->nullable()->after('credit_sense_report');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'credit_sense_app_id',
                'credit_sense_completed_at',
                'credit_sense_report',
                'credit_sense_report_received_at',
            ]);
        });
    }
};
