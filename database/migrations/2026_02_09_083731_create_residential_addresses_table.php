<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residential_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('address_type', ['current', 'previous_1', 'previous_2', 'previous_3']);
            $table->string('street_address');
            $table->string('suburb');
            $table->string('state');
            $table->string('postcode');
            $table->string('country')->default('Australia');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('months_at_address')->nullable();
            $table->enum('residential_status', ['own', 'rent', 'boarding', 'living_with_parents'])->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('address_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residential_addresses');
    }
};
