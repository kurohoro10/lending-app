<?php
// database/migrations/xxxx_xx_xx_create_company_assets_liabilities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('asset_name');
            $table->string('notes')->nullable();
            $table->decimal('value', 15, 2);
            $table->timestamps();

            $table->index('application_id');
        });

        Schema::create('company_liabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('liability_name');
            $table->string('notes')->nullable();
            $table->decimal('value', 15, 2);
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_liabilities');
        Schema::dropIfExists('company_assets');
    }
};
