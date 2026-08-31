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
            $table->json('business_declaration_data')->nullable()->after('loan_deed_signed_at');
            $table->timestamp('business_declaration_requested_at')->nullable()->after('business_declaration_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['business_declaration_data', 'business_declaration_requested_at']);
        });
    }
};
