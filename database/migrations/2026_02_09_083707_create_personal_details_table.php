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
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('mobile_phone')->unique()->nullable();
            $table->string('email')->unique();
            $table->enum('citizenship_status', [
                'australian_citizen',
                'permanent_resident',
                'temporary_resident',
                'nz_citizen'
            ])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'defacto']);
            $table->integer('number_of_dependants')->default(0);
            $table->string('spouse_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('mobile_phone');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_details');
    }
};
