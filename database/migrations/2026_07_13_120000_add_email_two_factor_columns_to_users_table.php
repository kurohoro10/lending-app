<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_method')->nullable()->after('two_factor_confirmed_at');
            $table->timestamp('email_two_factor_confirmed_at')->nullable()->after('two_factor_method');
            $table->string('email_otp_code')->nullable()->after('email_two_factor_confirmed_at');
            $table->timestamp('email_otp_expires_at')->nullable()->after('email_otp_code');
            $table->unsignedTinyInteger('email_otp_attempts')->default(0)->after('email_otp_expires_at');
        });

        DB::table('users')
            ->whereNotNull('two_factor_confirmed_at')
            ->update(['two_factor_method' => 'app']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_method',
                'email_two_factor_confirmed_at',
                'email_otp_code',
                'email_otp_expires_at',
                'email_otp_attempts',
            ]);
        });
    }
};
