<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('asked_by')->constrained('users')->onDelete('cascade');
            $table->text('question');
            $table->enum('question_type', ['structured', 'free_text', 'document_request', 'clarification']);
            $table->json('options')->nullable(); // For structured questions
            $table->text('answer')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('answered_at')->nullable();
            $table->ipAddress('answer_ip')->nullable();
            $table->enum('status', ['pending', 'answered', 'withdrawn'])->default('pending');
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();

            $table->index('application_id');
            $table->index('asked_by');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
