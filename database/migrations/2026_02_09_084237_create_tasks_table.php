<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('task_type', [
                'id_check',
                'living_expense_check',
                'declaration_verification',
                'credit_check',
                'document_review',
                'employment_verification',
                'other'
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('completion_notes')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('task_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
