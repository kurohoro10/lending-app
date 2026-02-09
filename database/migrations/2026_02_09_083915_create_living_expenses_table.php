<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('living_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('expense_category');
            $table->string('expense_name');
            $table->decimal('client_declared_amount', 10, 2)->default(0);
            $table->decimal('verified_amount', 10, 2)->nullable();
            $table->enum('frequency', ['weekly', 'fortnightly', 'monthly', 'quarterly', 'annual']);
            $table->text('client_notes')->nullable();
            $table->text('assessor_notes')->nullable();
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index('application_id');
            $table->index('expense_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('living_expenses');
    }
};
