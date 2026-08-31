<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * question_type has no default and no code path sets it on insert, which
     * fails under strict SQL mode (SQLSTATE[HY000]: 1364). The assessment
     * checklist feature bulk-inserts many rows per request, making this a
     * near-certain failure point, so it's fixed here as a prerequisite.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE questions MODIFY question_type ENUM('structured','free_text','document_request','clarification') NOT NULL DEFAULT 'clarification'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE questions MODIFY question_type ENUM('structured','free_text','document_request','clarification') NOT NULL");
    }
};
