<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('declaration_type'); // privacy, terms, accuracy, etc.
            $table->text('declaration_text');
            $table->boolean('is_agreed')->default(false);
            $table->timestamp('agreed_at')->nullable();
            $table->ipAddress('agreement_ip')->nullable();
            $table->string('signature')->nullable(); // Electronic signature data
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declarations');
    }
};
