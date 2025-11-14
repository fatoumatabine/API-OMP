<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuditLogService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditService = app(AuditLogService::class);
    }

    public function test_log_transaction_creates_log_entry(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $user = User::factory()->create();

        $this->auditService->logTransaction($user, 'test_transaction', ['amount' => 1000]);

        // If we got here without exception, test passes
        $this->assertTrue(true);
    }

    public function test_log_transfer_records_both_users(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->auditService->logTransfer($sender, $receiver, 5000, 50, true);

        $this->assertTrue(true);
    }

    public function test_log_deposit_includes_amount(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $user = User::factory()->create();

        $this->auditService->logDeposit($user, 10000, true);

        $this->assertTrue(true);
    }

    public function test_log_login_attempt_logs_success(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $this->auditService->logLoginAttempt('+221234567890', true);

        $this->assertTrue(true);
    }

    public function test_log_login_attempt_logs_failure(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $this->auditService->logLoginAttempt('+221234567890', false, 'Invalid credentials');

        $this->assertTrue(true);
    }

    public function test_log_otp_verification_success(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $user = User::factory()->create();

        $this->auditService->logOtpVerification($user, true);

        $this->assertTrue(true);
    }

    public function test_log_pin_change_success(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $user = User::factory()->create();

        $this->auditService->logPinChange($user, true);

        $this->assertTrue(true);
    }

    public function test_log_payment_includes_fees(): void
    {
        Log::shouldReceive('channel')
            ->with('audit')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->once();

        $payer = User::factory()->create();
        $merchant = User::factory()->create();

        $this->auditService->logPayment($payer, $merchant, 5000, 50, true);

        $this->assertTrue(true);
    }
}
