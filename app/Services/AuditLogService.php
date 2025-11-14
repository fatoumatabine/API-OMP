<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuditLogService
{
    /**
     * Log a transaction for audit trail
     */
    public function logTransaction(
        User $user,
        string $transactionType,
        array $data = [],
        string $status = 'completed'
    ): void {
        $logData = [
            'user_id' => $user->id,
            'user_phone' => $user->phone_number,
            'transaction_type' => $transactionType,
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            ...$data,
        ];

        Log::channel('audit')->info("Transaction: {$transactionType}", $logData);
    }

    /**
     * Log login attempt
     */
    public function logLoginAttempt(string $phoneNumber, bool $success, ?string $reason = null): void
    {
        $logData = [
            'phone_number' => $phoneNumber,
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($reason) {
            $logData['reason'] = $reason;
        }

        Log::channel('audit')->info(
            $success ? 'Login successful' : 'Login failed',
            $logData
        );
    }

    /**
     * Log OTP verification
     */
    public function logOtpVerification(User $user, bool $success, ?string $reason = null): void
    {
        $logData = [
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ];

        if ($reason) {
            $logData['reason'] = $reason;
        }

        Log::channel('audit')->info(
            $success ? 'OTP verification successful' : 'OTP verification failed',
            $logData
        );
    }

    /**
     * Log PIN change
     */
    public function logPinChange(User $user, bool $success, ?string $reason = null): void
    {
        $logData = [
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ];

        if ($reason) {
            $logData['reason'] = $reason;
        }

        Log::channel('audit')->info(
            $success ? 'PIN changed successfully' : 'PIN change failed',
            $logData
        );
    }

    /**
     * Log transfer
     */
    public function logTransfer(
        User $sender,
        User $receiver,
        float $amount,
        float $fees,
        bool $success,
        ?string $reason = null
    ): void {
        $logData = [
            'sender_id' => $sender->id,
            'sender_phone' => $sender->phone_number,
            'receiver_id' => $receiver->id,
            'receiver_phone' => $receiver->phone_number,
            'amount' => $amount,
            'fees' => $fees,
            'total' => $amount + $fees,
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ];

        if ($reason) {
            $logData['reason'] = $reason;
        }

        Log::channel('audit')->info(
            $success ? 'Transfer successful' : 'Transfer failed',
            $logData
        );
    }

    /**
     * Log deposit
     */
    public function logDeposit(User $user, float $amount, bool $success, ?string $reason = null): void
    {
        $logData = [
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
            'amount' => $amount,
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ];

        if ($reason) {
            $logData['reason'] = $reason;
        }

        Log::channel('audit')->info(
            $success ? 'Deposit successful' : 'Deposit failed',
            $logData
        );
    }

    /**
     * Log payment
     */
    public function logPayment(
        User $payer,
        User $merchant,
        float $amount,
        float $fees,
        bool $success,
        ?string $reason = null
    ): void {
        $logData = [
            'payer_id' => $payer->id,
            'payer_phone' => $payer->phone_number,
            'merchant_id' => $merchant->id,
            'merchant_phone' => $merchant->phone_number,
            'amount' => $amount,
            'fees' => $fees,
            'total' => $amount + $fees,
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ];

        if ($reason) {
            $logData['reason'] = $reason;
        }

        Log::channel('audit')->info(
            $success ? 'Payment successful' : 'Payment failed',
            $logData
        );
    }
}
