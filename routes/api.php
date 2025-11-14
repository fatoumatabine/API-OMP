<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Health Check (public)
Route::get('/health', [HealthController::class, 'health'])->name('health');
Route::get('/health/detailed', [HealthController::class, 'healthDetailed'])->name('health.detailed');

// Documentation Swagger
Route::get('/documentation', function () {
    return view('swagger-ui');
})->name('api.documentation');

// Authentication (avec rate limiting)
Route::middleware(['rate.limit'])->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
    Route::post('auth/resend-otp', [AuthController::class, 'resendOtp'])->name('auth.resend-otp');

    // Compte
    Route::post('register', [CompteController::class, 'register'])->middleware('log.creation')->name('register');
    Route::post('verify-otp', [CompteController::class, 'verifyOtp'])->middleware('log.creation')->name('verify-otp');
});

// Compte protégées (avec rate limiting)
Route::middleware(['auth:api', 'log.creation', 'rate.limit'])->group(function () {
    Route::get('compte/dashboard', [CompteController::class, 'dashboard'])->name('compte.dashboard');
    Route::get('comptes/{id}/solde', [CompteController::class, 'getSolde'])->name('compte.solde');
    Route::post('compte/{id}/depot', [CompteController::class, 'depot'])->name('compte.depot');
    Route::post('compte/{id}/payment', [CompteController::class, 'payment'])->name('compte.payment');
});


// Protected routes (avec rate limiting)
Route::middleware(['auth:api', 'log.creation', 'rate.limit'])->group(function () {
    // Authentication
    Route::post('auth/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
    Route::post('auth/change-pin', [AuthController::class, 'changePin'])->name('auth.change-pin');
    Route::post('auth/create-pin', [AuthController::class, 'createPin'])->name('auth.create-pin');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Wallet
    Route::get('wallet/balance', [WalletController::class, 'getBalance']);
    Route::post('wallet/deposit', [WalletController::class, 'deposit']);

    // Transactions
    Route::post('transactions/transfer', [TransactionController::class, 'transfer']);
    Route::get('transactions/history', [TransactionController::class, 'history']);
    Route::get('compte/{id}/transactions', [TransactionController::class, 'getTransactionsByAccount'])->name('transaction.by-account');
    Route::post('compte/{id}/payment-merchant', [TransactionController::class, 'payment'])->name('transaction.payment-merchant');
});
