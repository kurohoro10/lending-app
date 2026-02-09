<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // credit_sense, equifax, etc.
            $table->string('reference_number')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('credit_score')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_checks');
    }
};
