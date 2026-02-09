<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->enum('type', ['internal', 'client_visible', 'system'])->default('internal');
            $table->boolean('is_pinned')->default(false);
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('application_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
