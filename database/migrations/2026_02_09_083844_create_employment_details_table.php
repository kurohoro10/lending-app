<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('employment_type', [
                'payg',
                'self_employed',
                'company_director',
                'contract',
                'casual',
                'retired',
                'unemployed'
            ]);
            $table->string('employer_business_name')->nullable();
            $table->string('abn')->nullable();
            $table->string('employment_role')->nullable();
            $table->string('position')->nullable();
            $table->date('employment_start_date')->nullable();
            $table->integer('length_of_employment_months')->nullable();
            $table->decimal('base_income', 12, 2)->default(0);
            $table->decimal('additional_income', 12, 2)->default(0);
            $table->enum('income_frequency', ['weekly', 'fortnightly', 'monthly', 'annual']);
            $table->string('employer_phone')->nullable();
            $table->text('employer_address')->nullable();
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_details');
    }
};
