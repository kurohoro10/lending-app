<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('document_category'); // id, income, bank, assets, etc.
            $table->string('document_type')->nullable();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size'); // in bytes
            $table->text('description')->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('documents')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected', 'replaced'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->ipAddress('upload_ip')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('application_id');
            $table->index('document_category');
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
