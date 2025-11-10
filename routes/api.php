<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Groupement des routes par version (v1)
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

    // Compte
    Route::post('register', [CompteController::class, 'register'])->middleware('log.creation')->name('register');

    // Protected routes
    Route::middleware(['auth:api', 'log.creation'])->group(function () {
        // Wallet
        Route::get('wallet/balance', [WalletController::class, 'getBalance']);
        Route::post('wallet/deposit', [WalletController::class, 'deposit']);

        // Transactions
        Route::post('transactions/transfer', [TransactionController::class, 'transfer']);
        Route::get('transactions/history', [TransactionController::class, 'history']);
    });
});
