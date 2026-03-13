<?php
// database/migrations/xxxx_xx_xx_create_accountant_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountant_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            $table->string('accountant_name');
            $table->string('accountant_email')->nullable();
            $table->string('accountant_phone')->nullable();
            $table->integer('years_with_accountant')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountant_details');
    }
};
