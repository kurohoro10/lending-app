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
            $table->json('loan_deed_data')->nullable()->after('guarantor_required');
            $table->timestamp('loan_deed_requested_at')->nullable()->after('loan_deed_data');
            $table->text('loan_deed_request_url')->nullable()->after('loan_deed_requested_at');
            $table->timestamp('loan_deed_signed_at')->nullable()->after('loan_deed_request_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'loan_deed_data',
                'loan_deed_requested_at',
                'loan_deed_request_url',
                'loan_deed_signed_at',
            ]);
        });
    }
};
