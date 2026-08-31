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
        // 1. Add the new column.
        Schema::table('applications', function (Blueprint $table) {
            $table->integer('term_weeks')->nullable()->after('term_months');
        });

        // 2. Backfill from the old column (months -> weeks).
        DB::statement('
            UPDATE applications
            SET term_weeks = ROUND(term_months * 52 / 12)
            WHERE term_months IS NOT NULL
        ');

        // 3. Drop the old column.
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('term_months');
        });
    }

    public function down(): void
    {
        // 1. Re-add the old column.
        Schema::table('applications', function (Blueprint $table) {
            $table->integer('term_months')->nullable()->after('term_weeks');
        });

        // 2. Backfill from term_weeks (weeks -> months).
        DB::statement('
            UPDATE applications
            SET term_months = ROUND(term_weeks * 12 / 52)
            WHERE term_weeks IS NOT NULL
        ');

        // 3. Drop the new column.
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('term_weeks');
        });
    }
};
