<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('application_number')->unique();
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'additional_info_required',
                'approved',
                'declined',
                'withdrawn'
            ])->default('draft');
            $table->decimal('loan_amount', 15, 2)->nullable();
            $table->string('loan_purpose')->nullable();
            $table->text('loan_purpose_details')->nullable();
            $table->integer('term_months')->nullable();
            $table->string('security_type')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->ipAddress('submission_ip')->nullable();
            $table->text('return_reason')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('electronic_signature_id')->nullable();
            $table->timestamp('signature_signed_at')->nullable();
            $table->ipAddress('signature_ip')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('application_number');
            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
