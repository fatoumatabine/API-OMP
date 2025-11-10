<?php

namespace App\Interfaces;

use App\Models\User;

interface UserServiceInterface
{
    public function createUserForClient(array $data): User;
}
