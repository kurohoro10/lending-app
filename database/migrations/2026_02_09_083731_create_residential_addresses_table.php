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
            $table->enum('state', ['NSW', 'VIC', 'QLD', 'SA', 'WA', 'TAS', 'NT', 'ACT'])->nullable();
            $table->enum('residential_status', [
                'owner_no_mortgage',
                'owner_with_mortgage',
                'renting',
                'boarding',
                'living_with_parents',
                'other'
            ])->nullable();
            $table->string('postcode');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('months_at_address')->nullable();
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
