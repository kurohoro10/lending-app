<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update application statuses for new workflow system:
     * - apply (draft)
     * - assessment (submitted/wip)
     * - outstanding (outstanding_document)
     * - loan_doc_out (waiting_for_signature)
     * - wait_sign (new intermediate)
     * - sign (new signature phase)
     * - settled (approved)
     * - declined, deferred, withdrawn (unchanged)
     *
     * Also adds guarantor form tracking columns.
     */
    public function up(): void
    {
        // Step 1: Widen enum to accept both old and new values
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'wip',
                'outstanding_document',
                'waiting_for_signature',
                'apply',
                'assessment',
                'outstanding',
                'loan_doc_out',
                'wait_sign',
                'sign',
                'settled',
                'approved',
                'declined',
                'deferred',
                'withdrawn'
            ) NOT NULL DEFAULT 'apply'
        ");

        // Step 2: Migrate existing rows to new status names
        DB::statement("UPDATE applications SET status = 'apply' WHERE status = 'draft'");
        DB::statement("UPDATE applications SET status = 'assessment' WHERE status IN ('submitted', 'wip')");
        DB::statement("UPDATE applications SET status = 'outstanding' WHERE status = 'outstanding_document'");
        DB::statement("UPDATE applications SET status = 'loan_doc_out' WHERE status = 'waiting_for_signature'");
        DB::statement("UPDATE applications SET status = 'settled' WHERE status = 'approved'");

        // Step 3: Remove old enum values, keep only the new set
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'apply',
                'assessment',
                'outstanding',
                'loan_doc_out',
                'wait_sign',
                'sign',
                'settled',
                'declined',
                'deferred',
                'withdrawn'
            ) NOT NULL DEFAULT 'apply'
        ");

        // Step 4: Add guarantor form columns
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('guarantor_form_generated_at')->nullable()->after('signature_ip');
            $table->string('guarantor_form_path')->nullable()->after('guarantor_form_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Remove new columns
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['guarantor_form_generated_at', 'guarantor_form_path']);
        });

        // Step 2: Widen to accept both old and new values
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'wip',
                'outstanding_document',
                'waiting_for_signature',
                'apply',
                'assessment',
                'outstanding',
                'loan_doc_out',
                'wait_sign',
                'sign',
                'settled',
                'approved',
                'declined',
                'deferred',
                'withdrawn'
            ) NOT NULL DEFAULT 'draft'
        ");

        // Step 3: Reverse the rows
        DB::statement("UPDATE applications SET status = 'draft' WHERE status = 'apply'");
        DB::statement("UPDATE applications SET status = 'submitted' WHERE status = 'assessment'");
        DB::statement("UPDATE applications SET status = 'outstanding_document' WHERE status = 'outstanding'");
        DB::statement("UPDATE applications SET status = 'waiting_for_signature' WHERE status = 'loan_doc_out'");
        DB::statement("UPDATE applications SET status = 'approved' WHERE status = 'settled'");

        // Step 4: Back to original enum (from 2026_04_29 migration)
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'wip',
                'outstanding_document',
                'waiting_for_signature',
                'approved',
                'declined',
                'deferred',
                'withdrawn'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};