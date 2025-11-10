<?php

namespace App\Services;

use App\Interfaces\CompteServiceInterface;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompteService implements CompteServiceInterface
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function createCompte(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userService->createUserForClient($data);
            return $user;
        });
    }
}
