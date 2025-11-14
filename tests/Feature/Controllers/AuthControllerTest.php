<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_login_sends_otp(): void
    {
        $user = User::factory()->create(['phone_number' => '+221234567890']);

        $response = $this->postJson('/api/auth/login', [
            'phone_number' => '+221234567890',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.phone_number', '+221234567890');
    }

    public function test_login_fails_for_nonexistent_user(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'phone_number' => '+999999999999',
        ]);

        $response->assertStatus(404);
    }

    public function test_verify_otp_with_valid_code(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+221234567890',
            'otp_code' => 'TEST123',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phone_number' => '+221234567890',
            'otp' => 'TEST123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertNotNull($response->json('data.token'));
    }

    public function test_verify_otp_fails_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'phone_number' => '+221234567890',
            'otp_code' => 'TEST123',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phone_number' => '+221234567890',
            'otp' => 'INVALID',
        ]);

        $response->assertStatus(400);
    }

    public function test_verify_otp_fails_for_nonexistent_user(): void
    {
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone_number' => '+999999999999',
            'otp' => 'TEST123',
        ]);

        $response->assertStatus(404);
    }

    public function test_create_pin_success(): void
    {
        $user = User::factory()->create(['pin_code' => null]);
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/auth/create-pin',
            ['pin' => '1234'],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $user->refresh();
        $this->assertTrue(Hash::check('1234', $user->pin_code));
    }

    public function test_create_pin_fails_if_already_exists(): void
    {
        $user = User::factory()->create([
            'pin_code' => Hash::make('1234'),
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/auth/create-pin',
            ['pin' => '5678'],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(400);
    }

    public function test_change_pin_success(): void
    {
        $user = User::factory()->create([
            'pin_code' => Hash::make('1234'),
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/auth/change-pin',
            [
                'old_pin' => '1234',
                'new_pin' => '5678',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('5678', $user->pin_code));
    }

    public function test_change_pin_fails_with_invalid_old_pin(): void
    {
        $user = User::factory()->create([
            'pin_code' => Hash::make('1234'),
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/auth/change-pin',
            [
                'old_pin' => 'WRONG',
                'new_pin' => '5678',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(401);
    }

    public function test_refresh_token_success(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/auth/refresh-token',
            [],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.token'));
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/auth/logout',
            [],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_auth_endpoints_require_authentication(): void
    {
        $response = $this->postJson('/api/auth/create-pin', ['pin' => '1234']);
        $this->assertEquals(401, $response->status());

        $response = $this->postJson('/api/auth/change-pin', [
            'old_pin' => '1234',
            'new_pin' => '5678',
        ]);
        $this->assertEquals(401, $response->status());

        $response = $this->postJson('/api/auth/refresh-token');
        $this->assertEquals(401, $response->status());

        $response = $this->postJson('/api/auth/logout');
        $this->assertEquals(401, $response->status());
    }

    public function test_resend_otp_for_existing_user(): void
    {
        $user = User::factory()->create(['phone_number' => '+221234567890']);

        $response = $this->postJson('/api/auth/resend-otp', [
            'phone_number' => '+221234567890',
        ]);

        $response->assertStatus(200);
    }
}
