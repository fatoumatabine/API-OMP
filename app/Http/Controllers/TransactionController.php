<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Endpoints pour la gestion des transactions"
 * )
 */
class TransactionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/transactions/transfer",
     *     summary="Effectuer un transfert d'argent",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"receiver_phone", "amount"},
     *             @OA\Property(property="receiver_phone", type="string", example="771234568"),
     *             @OA\Property(property="amount", type="number", format="float", example=5000),
     *             @OA\Property(property="description", type="string", example="Transfert familial")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert effectué avec succès"
     *     )
     * )
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'receiver_phone' => 'required',
            'amount' => 'required|numeric|min:100',
            'description' => 'nullable|string'
        ]);

        $sender = Auth::user();
        $receiver = User::where('phone_number', $validated['receiver_phone'])->first();

        if (!$receiver) {
            return response()->json(['error' => 'Destinataire non trouvé'], 404);
        }

        if ($sender->wallet->balance < $validated['amount']) {
            return response()->json(['error' => 'Solde insuffisant'], 400);
        }

        return DB::transaction(function () use ($sender, $receiver, $validated) {
            // Calcul des frais (exemple: 1%)
            $fees = $validated['amount'] * 0.01;
            $totalAmount = $validated['amount'] + $fees;

            // Débit du compte expéditeur
            $sender->wallet->decrement('balance', $totalAmount);

            // Crédit du compte destinataire
            $receiver->wallet->increment('balance', $validated['amount']);

            // Création de la transaction
            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'sender_wallet_id' => $sender->wallet->id,
                'receiver_wallet_id' => $receiver->wallet->id,
                'amount' => $validated['amount'],
                'fees' => $fees,
                'type' => Transaction::TYPE_TRANSFER,
                'status' => Transaction::STATUS_COMPLETED,
                'reference' => 'TRF' . time(),
                'description' => $validated['description'] ?? 'Transfert d\'argent'
            ]);

            return response()->json([
                'message' => 'Transfert effectué avec succès',
                'transaction' => $transaction,
                'new_balance' => $sender->wallet->balance
            ]);
        });
    }

    /**
     * @OA\Get(
     *     path="/transactions/history",
     *     summary="Historique des transactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des transactions"
     *     )
     * )
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($transactions);
    }
}
