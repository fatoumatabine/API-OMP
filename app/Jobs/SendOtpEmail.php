<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOtpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $otpCode;

    public function __construct(User $user, string $otpCode)
    {
        $this->user = $user;
        $this->otpCode = $otpCode;
    }

    public function handle(): void
    {
        try {
            Mail::send('emails.otp', [
                'user' => $this->user,
                'otp_code' => $this->otpCode,
            ], function ($message) {
                $message->to($this->user->email)
                        ->subject('Votre code OTP - OMPAY');
            });
            Log::info("OTP email envoyé à {$this->user->email}");
        } catch (\Exception $e) {
            Log::error("Erreur envoi OTP à {$this->user->email}: " . $e->getMessage());
        }
    }
}
