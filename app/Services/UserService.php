<?php

namespace App\Services;

use App\Events\AccountCreated;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService implements UserServiceInterface
{
    public function createUserForClient(array $data): User
    {
        $userData = [
            'phone_number' => $data['phone_number'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'pin_code' => Hash::make($data['pin_code']),
            'cni_number' => $data['cni_number'],
            'kyc_status' => 'pending',
            // biometrics_active will use database default
            'last_login_at' => now(),
        ];

        // Ajouter le password seulement s'il existe, sinon générer un token aléatoire
        if (isset($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        } else {
            // Use a random token as placeholder until user sets password
            $userData['password'] = Hash::make(Str::random(32));
        }

        $user = User::create($userData);

        // Initialiser le portefeuille de l'utilisateur
        $user->initializeWallet();

        // Déclencher l'événement AccountCreated
        event(new AccountCreated($user));

        return $user;
    }
}
