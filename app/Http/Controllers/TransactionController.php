<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Requests\TransactionHistoryRequest;
use App\Http\Requests\TransferRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AuditLogService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Endpoints pour la gestion des transactions"
 * )
 */
class TransactionController extends Controller
{
    private AuditLogService $auditService;
    private CacheService $cacheService;

    public function __construct(AuditLogService $auditService, CacheService $cacheService)
    {
        $this->auditService = $auditService;
        $this->cacheService = $cacheService;
    }

    /**
     * @OA\Post(
     *     path="/api/transactions/transfer",
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
    public function transfer(TransferRequest $request)
    {
        $validated = $request->validated();

        /** @var User $sender */
        $sender = Auth::user();
        /** @var User $receiver */
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
            $sender->wallet->update(['balance' => DB::raw('balance - ' . $totalAmount)]);

            // Crédit du compte destinataire
            $receiver->wallet->update(['balance' => DB::raw('balance + ' . $validated['amount'])]);

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

            // Log audit trail
            $this->auditService->logTransfer($sender, $receiver, $validated['amount'], $fees, true);

            // Invalidate cache
            $this->cacheService->invalidateBalance($sender);
            $this->cacheService->invalidateBalance($receiver);
            $this->cacheService->invalidateAllHistory($sender);
            $this->cacheService->invalidateAllHistory($receiver);

            return response()->json([
                'message' => 'Transfert effectué avec succès',
                'transaction' => $transaction,
                'new_balance' => $sender->wallet->balance
            ]);
        });
    }

    /**
     * @OA\Get(
     *     path="/api/transactions/history",
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
    public function history(TransactionHistoryRequest $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        
        // Try to get from cache first
        $cached = $this->cacheService->getHistory($user, $page, $perPage);
        if ($cached !== null) {
            return response()->json($cached);
        }
        
        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Prepare response
        $response = [
            'data' => $transactions->items(),
            'current_page' => $transactions->currentPage(),
            'per_page' => $transactions->perPage(),
            'total' => $transactions->total(),
            'last_page' => $transactions->lastPage(),
        ];

        // Cache the response
        $this->cacheService->setHistory($user, $page, $perPage, $response);

        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/compte/{id}/transactions",
     *     summary="Historique des transactions d'un compte",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du wallet/compte",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique des transactions du compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transactions du compte"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="amount", type="number", format="float"),
     *                     @OA\Property(property="fees", type="number", format="float"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="reference", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur"
     *     )
     * )
     */
    public function getTransactionsByAccount($id)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Vérifier que le compte appartient à l'utilisateur
            /** @var Wallet $wallet */
            $wallet = $user->wallet()->where('id', $id)->first();

            if (!$wallet) {
                return response()->json(['error' => 'Compte non trouvé'], 404);
            }

            $transactions = Transaction::where(function ($query) use ($id) {
                $query->where('sender_wallet_id', $id)
                      ->orWhere('receiver_wallet_id', $id);
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'type', 'amount', 'fees', 'status', 'reference', 'description', 'created_at']);

            return response()->json([
                'message' => 'Transactions du compte',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/compte/{id}/payment-merchant",
     *     summary="Effectuer un paiement à un marchand",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du wallet/compte de l'émetteur",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"merchant_identifier", "amount"},
     *             @OA\Property(property="merchant_identifier", type="string", example="771234567"),
     *             @OA\Property(property="amount", type="number", format="float", example=5000.00),
     *             @OA\Property(property="description", type="string", example="Paiement marchand", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement marchand effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Paiement effectué avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction_id", type="string", format="uuid"),
     *                 @OA\Property(property="amount", type="number", format="float"),
     *                 @OA\Property(property="fees", type="number", format="float"),
     *                 @OA\Property(property="total_amount", type="number", format="float"),
     *                 @OA\Property(property="merchant_identifier", type="string"),
     *                 @OA\Property(property="new_balance", type="number", format="float"),
     *                 @OA\Property(property="merchant_new_balance", type="number", format="float"),
     *                 @OA\Property(property="reference", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé ou marchand non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur"
     *     )
     * )
     */
    public function payment(PaymentRequest $request, $id)
    {
        try {
            $validated = $request->validated();

            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Vérifier que le compte appartient à l'utilisateur
            /** @var Wallet $wallet */
            $wallet = $user->wallet()->where('id', $id)->first();

            if (!$wallet) {
                return response()->json(['error' => 'Compte non trouvé'], 404);
            }

            // Rechercher le marchand par son identifiant (numéro de téléphone ou ID)
            $merchantUser = User::where('phone_number', $validated['merchant_identifier'])->first();

            if (!$merchantUser) {
                return response()->json(['error' => 'Marchand non trouvé'], 404);
            }

            $merchantWallet = $merchantUser->wallet; // Accéder directement à la relation hasOne

            if (!$merchantWallet) {
                return response()->json(['error' => 'Portefeuille du marchand non trouvé'], 404);
            }

            // Vérifier le solde de l'utilisateur
            if ($wallet->balance < $validated['amount']) {
                return response()->json(['error' => 'Solde insuffisant'], 400);
            }

            return DB::transaction(function () use ($wallet, $user, $validated, $merchantUser, $merchantWallet) {
                // Calculer les frais (exemple: 1%)
                $fees = $validated['amount'] * 0.01;
                $totalAmount = $validated['amount'] + $fees;

                // Débiter le compte de l'utilisateur
                $wallet->update(['balance' => DB::raw('balance - ' . $totalAmount)]);

                // Créditer le compte du marchand
                $merchantWallet->update(['balance' => DB::raw('balance + ' . $validated['amount'])]);

                // Créer la transaction
                $transaction = Transaction::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $merchantUser->id,
                    'sender_wallet_id' => $wallet->id,
                    'receiver_wallet_id' => $merchantWallet->id,
                    'amount' => $validated['amount'],
                    'fees' => $fees,
                    'type' => Transaction::TYPE_PAYMENT, // Utiliser la constante
                    'status' => Transaction::STATUS_COMPLETED,
                    'reference' => 'PAY' . time(),
                    'description' => $validated['description'] ?? 'Paiement marchand - ' . $validated['merchant_identifier'],
                ]);

                $wallet->refresh();
                $merchantWallet->refresh();

                // Log audit trail
                $this->auditService->logPayment($user, $merchantUser, $validated['amount'], $fees, true);

                // Invalidate cache
                $this->cacheService->invalidateBalance($user);
                $this->cacheService->invalidateBalance($merchantUser);
                $this->cacheService->invalidateAllHistory($user);
                $this->cacheService->invalidateAllHistory($merchantUser);

                return response()->json([
                    'message' => 'Paiement effectué avec succès',
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'amount' => (float) $validated['amount'],
                        'fees' => (float) $fees,
                        'total_amount' => (float) $totalAmount,
                        'merchant_identifier' => $validated['merchant_identifier'],
                        'new_balance' => (float) $wallet->balance,
                        'merchant_new_balance' => (float) $merchantWallet->balance,
                        'reference' => $transaction->reference,
                        'created_at' => $transaction->created_at
                    ]
                ]);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Erreur de validation: ' . json_encode($e->errors())], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
