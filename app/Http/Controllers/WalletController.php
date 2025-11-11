<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
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
    /**
     * @OA\Get(
     *     path="/v1/wallet/balance",
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
        $wallet = $user->wallet;
        
        return response()->json([
            'balance' => $wallet->balance,
            'currency' => $wallet->currency
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/wallet/deposit",
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
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

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

            return response()->json([
                'message' => 'Dépôt effectué avec succès',
                'new_balance' => $wallet->balance,
                'transaction' => $transaction
            ]);
        });
    }
}
