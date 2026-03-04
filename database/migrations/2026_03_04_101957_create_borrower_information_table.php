<?php
// database/migrations/xxxx_xx_xx_create_borrower_information_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrower_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('borrower_name');
            $table->enum('borrower_type', ['individual', 'company', 'trust', 'other']);
            $table->string('abn', 11)->nullable();
            $table->string('nature_of_business')->nullable();
            $table->unsignedSmallInteger('years_in_business')->nullable();
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrower_information');
    }
};
