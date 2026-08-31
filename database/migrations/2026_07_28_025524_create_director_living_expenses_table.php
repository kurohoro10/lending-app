<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('director_living_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('borrower_director_id')->constrained()->onDelete('cascade');
            $table->json('expenses');
            $table->timestamp('submitted_at')->nullable();
            $table->string('submitted_ip')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('borrower_director_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('director_living_expenses');
    }
};
