<?php

namespace Tests\Feature\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
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
            'balance' => 100000,
            'status' => 'active',
        ]);
        return $user;
    }

    public function test_transfer_money_between_users(): void
    {
        $sender = $this->createUserWithWallet();
        $receiver = $this->createUserWithWallet();

        $token = JWTAuth::fromUser($sender);

        $response = $this->postJson(
            '/api/transactions/transfer',
            [
                'receiver_phone' => $receiver->phone_number,
                'amount' => 5000,
                'description' => 'Test transfer',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Transfert effectué avec succès');

        // Verify balances
        $sender->refresh();
        $receiver->refresh();
        
        // Sender balance should be reduced by amount + fees (1%)
        $this->assertEquals(94950, $sender->wallet->balance); // 100000 - 5000 - 50
        // Receiver balance should be increased by amount only
        $this->assertEquals(105000, $receiver->wallet->balance); // 100000 + 5000
    }

    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $sender = $this->createUserWithWallet();
        $sender->wallet->update(['balance' => 100]);

        $receiver = $this->createUserWithWallet();

        $token = JWTAuth::fromUser($sender);

        $response = $this->postJson(
            '/api/transactions/transfer',
            [
                'receiver_phone' => $receiver->phone_number,
                'amount' => 5000,
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'Solde insuffisant');
    }

    public function test_transfer_to_non_existent_user_fails(): void
    {
        $sender = $this->createUserWithWallet();
        $token = JWTAuth::fromUser($sender);

        $response = $this->postJson(
            '/api/transactions/transfer',
            [
                'receiver_phone' => '+999999999999',
                'amount' => 5000,
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(404);
        $response->assertJsonPath('error', 'Destinataire non trouvé');
    }

    public function test_get_transaction_history(): void
    {
        $user = $this->createUserWithWallet();
        $receiver = $this->createUserWithWallet();

        // Create some transactions
        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'sender_wallet_id' => $user->wallet->id,
                'receiver_wallet_id' => $receiver->wallet->id,
                'amount' => 1000 * ($i + 1),
                'fees' => 10,
                'type' => Transaction::TYPE_TRANSFER,
                'status' => Transaction::STATUS_COMPLETED,
                'reference' => 'TEST' . $i,
                'description' => 'Test transaction',
            ]);
        }

        $token = JWTAuth::fromUser($user);

        $response = $this->getJson(
            '/api/transactions/history',
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('total', 5);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_payment_to_merchant(): void
    {
        $payer = $this->createUserWithWallet();
        $merchant = $this->createUserWithWallet();

        $token = JWTAuth::fromUser($payer);

        $response = $this->postJson(
            "/api/compte/{$payer->wallet->id}/payment",
            [
                'amount' => 5000,
                'merchant_identifier' => $merchant->phone_number,
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Paiement effectué avec succès');

        // Verify balances
        $payer->refresh();
        $merchant->refresh();
        
        $this->assertEquals(94950, $payer->wallet->balance); // 100000 - 5000 - 50 (fee)
        $this->assertEquals(105000, $merchant->wallet->balance); // 100000 + 5000
    }

    public function test_get_account_transactions(): void
    {
        $user = $this->createUserWithWallet();
        $receiver = $this->createUserWithWallet();

        // Create transaction
        Transaction::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'sender_wallet_id' => $user->wallet->id,
            'receiver_wallet_id' => $receiver->wallet->id,
            'amount' => 1000,
            'fees' => 10,
            'type' => Transaction::TYPE_TRANSFER,
            'status' => Transaction::STATUS_COMPLETED,
            'reference' => 'TEST001',
            'description' => 'Test',
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->getJson(
            "/api/compte/{$user->wallet->id}/transactions",
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_transaction_requires_authentication(): void
    {
        $response = $this->getJson('/api/transactions/history');

        $response->assertStatus(401);
    }
}
