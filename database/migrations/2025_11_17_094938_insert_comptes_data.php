<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('comptes')->insert([
            [
                'id' => Str::uuid(),
                'user_id' => Str::uuid(),
                'account_number' => '3000001',
                'solde' => 250000,
                'devise' => 'XOF',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => Str::uuid(),
                'account_number' => '3000002',
                'solde' => 300000,
                'devise' => 'XOF',
                'status' => 'suspended',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => Str::uuid(),
                'account_number' => '3000003',
                'solde' => 500000,
                'devise' => 'XOF',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('comptes')->whereIn('account_number', ['3000001', '3000002', '3000003'])->delete();
    }
};
