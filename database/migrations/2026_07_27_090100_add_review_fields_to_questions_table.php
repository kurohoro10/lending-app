<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds an independent review pipeline for checklist-originated questions
     * without touching the existing `status` column at all. `status` keeps
     * meaning exactly what it means today (has the client acted) for every
     * question regardless of origin — freeform or checklist. `review_status`
     * is a second, orthogonal fact: null for anything outside the assessment
     * checklist workflow (all existing/freeform questions), and one of
     * pending|awaiting_review|approved|returned for checklist items. It's a
     * plain nullable string (not a DB enum) to match the existing
     * doc_category_hint column's own type/validation convention.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('review_status')->nullable()->after('doc_category_hint');
            $table->text('review_notes')->nullable()->after('read_at');
            $table->foreignId('reviewed_by')->nullable()->after('review_notes')
                  ->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->index('review_status');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['review_status', 'review_notes', 'reviewed_by', 'reviewed_at']);
        });
    }
};
