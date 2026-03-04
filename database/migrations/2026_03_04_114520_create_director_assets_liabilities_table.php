<?php
// database/migrations/xxxx_xx_xx_create_director_assets_liabilities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('director_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('asset_type', ['house', 'bank', 'super', 'vehicle', 'other']);
            $table->string('description')->nullable();
            $table->enum('property_use', ['main_residence', 'rental', 'na'])->default('na');
            $table->decimal('estimated_value', 15, 2);
            $table->timestamps();

            $table->index('application_id');
        });

        Schema::create('director_liabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('liability_type', ['credit_card', 'home_loan', 'car_loan', 'other']);
            $table->string('lender_name')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->decimal('outstanding_balance', 15, 2);
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director_liabilities');
        Schema::dropIfExists('director_assets');
    }
};
