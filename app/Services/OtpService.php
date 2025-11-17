<?php

namespace App\Services;

use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
     * Envoyer l'OTP par email (synchrone ou via queue selon la config)
     */
    private function sendOtpByEmail(User $user, string $otpCode): void
    {
        try {
            Log::info("OTP généré pour {$user->email}: {$otpCode}");
            
            // Toujours utiliser le mode synchrone en production pour éviter les problèmes de queue
            $this->sendDirectly($user, $otpCode);
        } catch (\Exception $e) {
            Log::error('Erreur OTP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envoyer l'OTP via queue (asynchrone)
     */
    private function sendDirectly(User $user, string $otpCode): void
    {
        try {
            Log::info("Envoi OTP en queue à {$user->email}");
            
            // Vérifier que nous avons une adresse email valide
            if (!$user->email || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Email invalide pour l'utilisateur: " . ($user->email ?? 'NULL'));
            }
            
            // Queue l'email avec la classe Mailable
            Mail::queue(new OtpMail($user, $otpCode));
            
            Log::info("OTP email ajouté à la queue avec succès pour {$user->email}");
        } catch (\Exception $e) {
            Log::error("Erreur ajout OTP à queue pour {$user->email}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            
            throw $e;
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
