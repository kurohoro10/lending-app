<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mobile_phone')->nullable();
            $table->enum('citizenship_status', [
                'australian_citizen',
                'permanent_resident',
                'temporary_resident',
                'nz_citizen'
            ])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'defacto']);
            $table->integer('number_of_dependants')->default(0);
            $table->string('spouse_name')->nullable();
            $table->decimal('spouse_income', 12, 2)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('contact_role')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('mobile_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_details');
    }
};
