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
            $table->string('pin_code')->nullable();
            $table->string('cni_number')->unique()->nullable();
            $table->string('kyc_status')->default('pending');
            $table->boolean('biometrics_active')->default(false);
            $table->timestamp('last_login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin_code', 'cni_number', 'kyc_status', 'biometrics_active', 'last_login_at']);
        });
    }
};
