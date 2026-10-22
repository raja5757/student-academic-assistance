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
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_otp')->nullable()->after('email_verified_at');
            $table->timestamp('verification_otp_expires_at')->nullable()->after('verification_otp');
            $table->timestamp('otp_last_sent_at')->nullable()->after('verification_otp_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'verification_otp',
                'verification_otp_expires_at',
                'otp_last_sent_at',
            ]);
        });
    }
};
