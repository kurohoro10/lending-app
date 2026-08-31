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
        Schema::create('director_asset_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('director_asset_id');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index('director_asset_id');
            $table->foreign('director_asset_id')
                ->references('id')->on('director_assets')->onDelete('cascade');
        });

        Schema::create('director_liability_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('director_liability_id');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index('director_liability_id');
            $table->foreign('director_liability_id')
                ->references('id')->on('director_liabilities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('director_asset_liability_histories');
    }
};
