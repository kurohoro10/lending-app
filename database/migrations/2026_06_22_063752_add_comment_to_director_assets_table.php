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
        Schema::table('director_assets', function (Blueprint $table) {
            $table->text('comment')->nullable()->after('ownership_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('director_assets', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
};
