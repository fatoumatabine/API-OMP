<?php

namespace App\Services;

use App\Interfaces\CompteServiceInterface;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use App\Models\Compte;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompteService implements CompteServiceInterface
{
    protected $userService;
    protected $otpService;

    public function __construct(UserServiceInterface $userService, OtpService $otpService)
    {
        $this->userService = $userService;
        $this->otpService = $otpService;
    }

    /**
     * Initier l'enregistrement avec envoi du code OTP
     */
    public function initiateRegistration(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Créer l'utilisateur avec status "unverified"
            $data['status'] = 'unverified';
            $data['is_verified'] = false;
            // Pas de password à cette étape
            unset($data['password']);
            
            $user = $this->userService->createUserForClient($data);
            
            // Créer automatiquement un compte pour l'utilisateur
            $this->createUserCompte($user);
            
            // Générer et envoyer l'OTP
            $this->otpService->generateAndSendOtp($user);
            
            return $user;
        });
    }

    /**
     * Vérifier l'OTP et définir le mot de passe
     */
    public function verifyOtpAndSetPassword(User $user, string $otpCode, string $password): User
    {
        return DB::transaction(function () use ($user, $otpCode, $password) {
            // Vérifier l'OTP
            if (!$this->otpService->verifyOtp($user, $otpCode)) {
                throw new \Exception('Le code OTP est invalide ou a expiré.');
            }

            // Définir le mot de passe et marquer comme vérifié
            DB::update(
                "UPDATE users SET password = ?, is_verified = true::boolean, status = ?, updated_at = ? WHERE id = ?",
                [Hash::make($password), 'verified', now(), $user->id]
            );
            
            // Recharger l'utilisateur depuis la BD
            $user = $user->fresh();

            // Nettoyer le code OTP
            $this->otpService->clearOtp($user);

            return $user;
        });
    }

    public function createCompte(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userService->createUserForClient($data);
            return $user;
        });
    }

    /**
     * Créer un compte pour un utilisateur
     */
    private function createUserCompte(User $user): Compte
    {
        return Compte::create([
            'user_id' => $user->id,
            'account_number' => $this->generateAccountNumber(),
            'solde' => 0,
            'devise' => 'XOF',
            'status' => 'active',
        ]);
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateAccountNumber(): string
    {
        do {
            $accountNumber = 'OMP' . date('Y') . str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Compte::where('account_number', $accountNumber)->exists());
        
        return $accountNumber;
    }
}
