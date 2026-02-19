<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Null = not yet completed. Populated when CreditSense JS callback fires.
            $table->timestamp('credit_sense_completed_at')->nullable()->after('submission_ip');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('credit_sense_completed_at');
        });
    }
};
