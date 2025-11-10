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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('sender_wallet_id')->nullable()->constrained('wallets')->onDelete('set null');
            $table->foreignUuid('receiver_wallet_id')->nullable()->constrained('wallets')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->decimal('fees', 8, 2)->default(0);
            $table->string('type'); // deposit, withdrawal, transfer, payment
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
