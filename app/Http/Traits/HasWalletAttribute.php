<?php

namespace App\Http\Traits;

use App\Models\Wallet;

trait HasWalletAttribute
{
    public function getSoldeAttribute(): float
    {
        return $this->wallet ? $this->wallet->balance : 0.00;
    }

    public function initializeWallet(): void
    {
        if (!$this->wallet) {
            $this->wallet()->create([
                'balance' => 0,
                'currency' => 'XOF',
                'last_updated' => now(),
            ]);
        }
    }
}
