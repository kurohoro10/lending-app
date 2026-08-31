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
        // Add assessor fields to the existing employment_details table
        Schema::table('employment_details', function (Blueprint $table) {
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null')->after('application_id');
            $table->text('comment')->nullable()->after('employer_address');
        });

        // History log for edits to employment records
        Schema::create('employment_detail_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employment_detail_id');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index('employment_detail_id', 'edh_emp_id_index');
            $table->foreign('employment_detail_id', 'edh_emp_id_foreign')
                  ->references('id')->on('employment_details')->onDelete('cascade');
        });

        // Unlock + stamp tracking
        Schema::create('assessor_employment_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade')->unique();
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('verified_at')->nullable();

            $table->index('application_id');
        });

        // Documents uploaded per employment record by assessor
        Schema::create('assessor_employment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employment_detail_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index('employment_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessor_employment_documents');
        Schema::dropIfExists('assessor_employment_verifications');
        Schema::dropIfExists('employment_detail_histories');

        Schema::table('employment_details', function (Blueprint $table) {
            $table->dropForeign(['added_by']);
            $table->dropColumn(['added_by', 'comment']);
        });
    }
};
