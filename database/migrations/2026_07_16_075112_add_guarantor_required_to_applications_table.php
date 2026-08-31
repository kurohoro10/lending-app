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
        // Column may already exist: it was originally added by a migration file
        // that was applied and later deleted from disk.
        if (Schema::hasColumn('applications', 'guarantor_required')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('guarantor_required')->default(true)->after('guarantor_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('guarantor_required');
        });
    }
};
