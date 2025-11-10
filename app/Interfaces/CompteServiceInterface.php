<?php

namespace App\Interfaces;

use App\Models\User;

interface CompteServiceInterface
{
    public function createCompte(array $data): User;
}
