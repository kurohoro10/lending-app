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
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('sent_to_client')->default(false)->after('completion_notes');
            $table->timestamp('sent_to_client_at')->nullable()->after('sent_to_client');
            $table->text('client_response')->nullable()->after('sent_to_client_at');
            $table->timestamp('client_responded_at')->nullable()->after('client_response');
            $table->string('response_token')->nullable()->unique()->after('client_responded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'sent_to_client',
                'sent_to_client_at',
                'client_response',
                'client_responded_at',
                'response_token',
            ]);
        });
    }
};
