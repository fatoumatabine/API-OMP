<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createUserWithWallet(): User
    {
        $user = User::factory()->create();
        $user->wallet()->create([
            'currency' => 'XOF',
            'balance' => 50000,
            'status' => 'active',
        ]);
        return $user;
    }

    public function test_get_wallet_balance(): void
    {
        $user = $this->createUserWithWallet();
        $token = JWTAuth::fromUser($user);

        $response = $this->getJson(
            '/api/wallet/balance',
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('balance', 50000);
        $response->assertJsonPath('currency', 'XOF');
    }

    public function test_get_balance_caches_result(): void
    {
        $user = $this->createUserWithWallet();
        $token = JWTAuth::fromUser($user);

        // First request should not be cached
        $response1 = $this->getJson(
            '/api/wallet/balance',
            ['Authorization' => "Bearer $token"]
        );
        $this->assertEquals(false, $response1->json('cached'));

        // Second request should be cached
        $response2 = $this->getJson(
            '/api/wallet/balance',
            ['Authorization' => "Bearer $token"]
        );
        $this->assertEquals(true, $response2->json('cached'));
    }

    public function test_deposit_money_to_wallet(): void
    {
        $user = $this->createUserWithWallet();
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/wallet/deposit',
            ['amount' => 10000],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Dépôt effectué avec succès');
        $response->assertJsonPath('new_balance', 60000);

        // Verify in database
        $user->refresh();
        $this->assertEquals(60000, $user->wallet->balance);
    }

    public function test_deposit_creates_transaction_record(): void
    {
        $user = $this->createUserWithWallet();
        $token = JWTAuth::fromUser($user);

        $this->postJson(
            '/api/wallet/deposit',
            ['amount' => 10000],
            ['Authorization' => "Bearer $token"]
        );

        $transaction = $user->wallet->receiverTransactions()->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(10000, $transaction->amount);
        $this->assertEquals('deposit', $transaction->type);
        $this->assertEquals('completed', $transaction->status);
    }

    public function test_deposit_requires_valid_amount(): void
    {
        $user = $this->createUserWithWallet();
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson(
            '/api/wallet/deposit',
            ['amount' => -1000],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(422);
    }

    public function test_wallet_requires_authentication(): void
    {
        $response = $this->getJson('/api/wallet/balance');

        $response->assertStatus(401);
    }

    public function test_deposit_requires_authentication(): void
    {
        $response = $this->postJson('/api/wallet/deposit', ['amount' => 10000]);

        $response->assertStatus(401);
    }

    public function test_multiple_deposits_accumulate(): void
    {
        $user = $this->createUserWithWallet();
        $token = JWTAuth::fromUser($user);

        $this->postJson(
            '/api/wallet/deposit',
            ['amount' => 10000],
            ['Authorization' => "Bearer $token"]
        );

        $this->postJson(
            '/api/wallet/deposit',
            ['amount' => 5000],
            ['Authorization' => "Bearer $token"]
        );

        $user->refresh();
        $this->assertEquals(65000, $user->wallet->balance);
    }
}
