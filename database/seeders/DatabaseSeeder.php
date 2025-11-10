<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone_number' => '+22245678901',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'pin_code' => bcrypt('1234'),
            'cni_number' => '1234567890ABC',
            'kyc_status' => 'verified',
            'biometrics_active' => false,
            'last_login_at' => now(),
        ]);
    }
}
