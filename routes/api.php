<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Documentation Swagger
Route::get('/documentation', function () {
    return view('swagger-ui');
})->name('api.documentation');

// Authentication
Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
Route::post('auth/resend-otp', [AuthController::class, 'resendOtp'])->name('auth.resend-otp');

// Compte
Route::post('register', [CompteController::class, 'register'])->middleware('log.creation')->name('register');


// Protected routes
Route::middleware(['auth:api', 'log.creation'])->group(function () {
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
});
