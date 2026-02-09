<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // document_requested, question_asked, status_changed, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional structured data
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('send_email')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('application_id');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
