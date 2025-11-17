<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Models\Transaction;
use App\Services\AuditLogService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Wallet",
 *     description="Endpoints pour la gestion du portefeuille"
 * )
 */
class WalletController extends Controller
{
    private AuditLogService $auditService;
    private CacheService $cacheService;

    public function __construct(AuditLogService $auditService, CacheService $cacheService)
    {
        $this->auditService = $auditService;
        $this->cacheService = $cacheService;
    }

    /**
     * @OA\Get(
     *     path="/api/wallet/balance",
     *     summary="Obtenir le solde du compte",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Solde récupéré avec succès"
     *     )
     * )
     */
    public function getBalance()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Try to get from cache first
        $cached = $this->cacheService->getBalance($user);
        if ($cached !== null) {
            return response()->json([
                'balance' => $cached,
                'currency' => $user->wallet->currency,
                'cached' => true
            ]);
        }
        
        $wallet = $user->wallet;
        
        // Cache the balance
        $this->cacheService->setBalance($user, (string)$wallet->balance);
        
        return response()->json([
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
            'cached' => false
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/wallet/deposit",
     *     summary="Dépôt d'argent",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", example=10000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dépôt effectué avec succès"
     *     )
     * )
     */
    public function deposit(DepositRequest $request)
    {
        $validated = $request->validated();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $wallet = $user->wallet;

        return DB::transaction(function () use ($user, $wallet, $validated) {
            $wallet->increment('balance', $validated['amount']);

            $transaction = Transaction::create([
                'sender_wallet_id' => null,
                'receiver_wallet_id' => $wallet->id,
                'receiver_id' => $user->id,
                'amount' => $validated['amount'],
                'fees' => 0,
                'type' => Transaction::TYPE_DEPOSIT,
                'status' => Transaction::STATUS_COMPLETED,
                'reference' => 'DEP' . time(),
                'description' => 'Dépôt d\'argent'
            ]);

            // Log audit trail
            $this->auditService->logDeposit($user, $validated['amount'], true);

            // Invalidate cache
            $this->cacheService->invalidateBalance($user);
            $this->cacheService->invalidateAllHistory($user);

            return response()->json([
                'message' => 'Dépôt effectué avec succès',
                'new_balance' => $wallet->balance,
                'transaction' => $transaction
            ]);
        });
    }
}
