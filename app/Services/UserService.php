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
        $user = User::create([
            'phone_number' => $data['phone_number'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'pin_code' => Hash::make($data['pin_code']),
            'cni_number' => $data['cni_number'],
            'kyc_status' => 'pending',
            'biometrics_active' => false,
            'last_login_at' => now(),
        ]);

        // Initialiser le portefeuille de l'utilisateur
        $user->initializeWallet();

        // Déclencher l'événement AccountCreated
        event(new AccountCreated($user));

        return $user;
    }
}
