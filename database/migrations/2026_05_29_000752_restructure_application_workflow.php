<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restructure application statuses to 7-step workflow:
     * - application (initial submission)
     * - wip (work in progress)
     * - outdoc (outstanding document)
     * - approved (forms & signatures phase)
     * - declined (terminal)
     * - deferred (terminal)
     * - settled (terminal/complete)
     *
     * Add columns for multi-step approved workflow tracking.
     */
    public function up(): void
    {
        // Step 1: Widen ENUM to accept both old and new values
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'application',
                'wip',
                'outdoc',
                'approved',
                'declined',
                'deferred',
                'settled',
                'apply',
                'assessment',
                'outstanding',
                'loan_doc_out',
                'wait_sign',
                'sign',
                'draft',
                'submitted',
                'outstanding_document',
                'waiting_for_signature',
                'approved_old',
                'withdrawn'
            ) NOT NULL DEFAULT 'application'
        ");

        // Step 2: Migrate existing data to new status names
        DB::statement("UPDATE applications SET status = 'application' WHERE status IN ('draft', 'apply', 'submitted')");
        DB::statement("UPDATE applications SET status = 'wip' WHERE status IN ('wip', 'assessment')");
        DB::statement("UPDATE applications SET status = 'outdoc' WHERE status IN ('outstanding_document', 'outstanding')");
        DB::statement("UPDATE applications SET status = 'approved' WHERE status IN ('waiting_for_signature', 'loan_doc_out', 'wait_sign', 'sign', 'approved', 'approved_old')");
        DB::statement("UPDATE applications SET status = 'settled' WHERE status IN ('settled')");
        // declined, deferred remain unchanged

        // Step 3: Narrow ENUM to only new values
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'application',
                'wip',
                'outdoc',
                'approved',
                'declined',
                'deferred',
                'settled'
            ) NOT NULL DEFAULT 'application'
        ");

        // Step 4: Add new tracking columns
        Schema::table('applications', function (Blueprint $table) {
            // Approval letter
            $table->timestamp('approval_letter_sent_at')->nullable()->after('status');
            
            // Guarantor form workflow
            $table->timestamp('guarantor_form_requested_at')->nullable()->after('approval_letter_sent_at');
            $table->string('guarantor_form_request_url')->nullable()->after('guarantor_form_requested_at');
            $table->timestamp('guarantor_form_completed_at')->nullable()->after('guarantor_form_request_url');
            $table->timestamp('guarantor_form_signed_at')->nullable()->after('guarantor_form_completed_at');
            
            // Business declaration
            $table->timestamp('business_declaration_sent_at')->nullable()->after('guarantor_form_signed_at');
            $table->timestamp('business_declaration_signed_at')->nullable()->after('business_declaration_sent_at');
            
            // Decline workflow
            $table->timestamp('decline_letter_sent_at')->nullable()->after('business_declaration_signed_at');
            $table->text('decline_reason')->nullable()->after('decline_letter_sent_at');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // Remove new columns
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'approval_letter_sent_at',
                'guarantor_form_requested_at',
                'guarantor_form_request_url',
                'guarantor_form_completed_at',
                'guarantor_form_signed_at',
                'business_declaration_sent_at',
                'business_declaration_signed_at',
                'decline_letter_sent_at',
                'decline_reason',
            ]);
        });

        // Revert to old ENUM with old values
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
