<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendOtpEmail;

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
            
            // En production: envoyer directement (synchrone)
            // Ou via queue job si QUEUE_CONNECTION=database
            if (config('queue.default') === 'sync') {
                // Mode synchrone (direct)
                $this->sendDirectly($user, $otpCode);
            } else {
                // Mode asynchrone (queue job)
                SendOtpEmail::dispatch($user, $otpCode);
                Log::info("Job SendOtpEmail dispatché pour {$user->email}");
            }
        } catch (\Exception $e) {
            Log::error('Erreur OTP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envoyer l'OTP directement sans queue
     */
    private function sendDirectly(User $user, string $otpCode): void
    {
        try {
            // Log de la configuration SMTP
            $config = [
                'mailer' => config('mail.mailer'),
                'host' => config('mail.host'),
                'port' => config('mail.port'),
                'username' => config('mail.username'),
                'from' => config('mail.from.address'),
                'queue' => config('queue.default'),
            ];
            
            fwrite(STDERR, "[OTP] Configuration SMTP: " . json_encode($config) . "\n");
            Log::info("Configuration SMTP:", $config);
            
            fwrite(STDERR, "[OTP] Envoi OTP direct à {$user->email}\n");
            Log::info("Envoi OTP direct à {$user->email}");
            
            Mail::send('emails.otp', [
                'user' => $user,
                'otp_code' => $otpCode,
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Votre code OTP - OMPAY')
                        ->from(config('mail.from.address'));
            });
            
            fwrite(STDERR, "[OTP] Email envoyé avec succès à {$user->email}\n");
            Log::info("OTP email envoyé avec succès à {$user->email}");
        } catch (\Exception $e) {
            $errorMsg = "Erreur envoi OTP direct à {$user->email}: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString();
            fwrite(STDERR, "[OTP ERROR] " . $errorMsg . "\n");
            
            Log::error("Erreur envoi OTP direct à {$user->email}: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'mail_config' => [
                    'mailer' => config('mail.mailer'),
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                ]
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
