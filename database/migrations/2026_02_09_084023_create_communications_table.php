<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['email_in', 'email_out', 'sms_in', 'sms_out', 'system_notification']);
            $table->string('direction'); // inbound, outbound
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('metadata')->nullable(); // For storing additional data like SMS provider response
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'read'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('external_id')->nullable(); // Provider message ID
            $table->ipAddress('sender_ip')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
