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
        // Only changes the column's DEFAULT for future inserts — does not touch
        // the guarantor_required value already stored on any existing application.
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('guarantor_required')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('guarantor_required')->default(true)->change();
        });
    }
};
