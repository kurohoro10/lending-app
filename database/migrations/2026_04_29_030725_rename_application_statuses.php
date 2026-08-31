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
        // Step 1: Widen to accept both old and new values
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'under_review',
                'additional_info_required',
                'wip',
                'outstanding_document',
                'waiting_for_signature',
                'approved',
                'declined',
                'deferred',
                'withdrawn'
            ) NOT NULL DEFAULT 'draft'
        ");

        // Step 2: Migrate existing rows
        DB::statement("UPDATE applications SET status = 'wip' WHERE status = 'under_review'");
        DB::statement("UPDATE applications SET status = 'outstanding_document' WHERE status = 'additional_info_required'");

        // Step 3: Remove the old values, keep only the final set
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Widen to accept both
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'under_review',
                'additional_info_required',
                'wip',
                'outstanding_document',
                'waiting_for_signature',
                'approved',
                'declined',
                'deferred',
                'withdrawn'
            ) NOT NULL DEFAULT 'draft'
        ");

        // Step 2: Reverse the rows
        DB::statement("UPDATE applications SET status = 'under_review' WHERE status = 'wip'");
        DB::statement("UPDATE applications SET status = 'additional_info_required' WHERE status = 'outstanding_document'");

        // Step 3: Back to original enum
        DB::statement("
            ALTER TABLE applications
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'under_review',
                'additional_info_required',
                'approved',
                'declined',
                'withdrawn'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
