<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpService
{
    /**
     * Générer et envoyer un code OTP
     */
    public function generateAndSendOtp(User $user): void
    {
        // Générer un code OTP de 6 chiffres
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Sauvegarder le code OTP et définir l'expiration (10 minutes)
        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Envoyer l'OTP par email
        $this->sendOtpByEmail($user, $otpCode);
        
        // Vous pouvez aussi ajouter SMS ici
        // $this->sendOtpBySms($user, $otpCode);
    }

    /**
     * Envoyer l'OTP par email
     */
    private function sendOtpByEmail(User $user, string $otpCode): void
    {
        // Utiliser une Mailable ou implémenter directement
        try {
            Mail::send('emails.otp', [
                'user' => $user,
                'otp_code' => $otpCode,
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Votre code OTP - OMPAY');
            });
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer le flux
            \Log::error('Erreur lors de l\'envoi de l\'OTP: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier le code OTP
     */
    public function verifyOtp(User $user, string $otpCode): bool
    {
        // Vérifier que le code OTP correspond et n'a pas expiré
        if ($user->otp_code === $otpCode && $user->otp_expires_at > Carbon::now()) {
            return true;
        }
        return false;
    }

    /**
     * Réinitialiser le code OTP après utilisation
     */
    public function clearOtp(User $user): void
    {
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
    }
}
