<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ComptesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('comptes')->insert([
            [
                'id' => Str::uuid(),
                'user_id' => Str::uuid(),
                'account_number' => '1000001',
                'solde' => 50000,
                'devise' => 'XOF',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => Str::uuid(),
                'account_number' => '1000002',
                'solde' => 75000,
                'devise' => 'XOF',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => Str::uuid(),
                'account_number' => '1000003',
                'solde' => 100000,
                'devise' => 'XOF',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
