<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\CompteService;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Compte",
 *     description="API Endpoints pour la gestion des comptes utilisateurs"
 * )
 */
class CompteController extends Controller
{
    use ApiResponseTrait;

    protected $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Initier l'enregistrement avec envoi du code OTP",
     *     tags={"Compte"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number", "first_name", "last_name", "email", "pin_code", "cni_number"},
     *             @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="pin_code", type="string", example="1234"),
     *             @OA\Property(property="cni_number", type="string", example="1234567890ABC"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Un code OTP a été envoyé à votre email."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="a1b2c3d4-e5f6-7890-1234-567890abcdef"),
     *                 @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="status", type="string", example="unverified"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->compteService->initiateRegistration($request->validated());
            return $this->successResponse($user, 'Un code OTP a été envoyé à votre email.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de l\'enregistrement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/verify-otp",
     *     summary="Vérifier le code OTP et définir le mot de passe",
     *     tags={"Compte"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number", "otp_code", "password", "password_confirmation"},
     *             @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *             @OA\Property(property="otp_code", type="string", example="123456"),
     *             @OA\Property(property="password", type="string", format="password", example="SecurePassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="SecurePassword123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte vérifié avec succès."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="a1b2c3d4-e5f6-7890-1234-567890abcdef"),
     *                 @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *                 @OA\Property(property="status", type="string", example="verified"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code OTP invalide ou expiré"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     )
     * )
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            // Trouver l'utilisateur par numéro de téléphone
            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user) {
                return $this->errorResponse('Utilisateur non trouvé.', 404);
            }

            if ($user->is_verified) {
                return $this->errorResponse('Ce compte est déjà vérifié.', 400);
            }

            // Vérifier l'OTP et définir le mot de passe
            $user = $this->compteService->verifyOtpAndSetPassword(
                $user,
                $request->otp,
                $request->password ?? ''
            );

            return $this->successResponse(null, 'Compte vérifié avec succès.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/compte/dashboard",
     *     summary="Obtenir le tableau de bord du compte utilisateur",
     *     tags={"Compte"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tableau de bord obtenu avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tableau de bord chargé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="phone_number", type="string"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string"),
     *                     @OA\Property(property="email", type="string", format="email"),
     *                     @OA\Property(property="kyc_status", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 ),
     *                 @OA\Property(property="compte", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="account_number", type="string"),
     *                     @OA\Property(property="balance", type="number", format="float"),
     *                     @OA\Property(property="currency", type="string"),
     *                     @OA\Property(property="qr_code", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 ),
     *                 @OA\Property(property="transactions", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="type", type="string"),
     *                         @OA\Property(property="amount", type="number", format="float"),
     *                         @OA\Property(property="status", type="string"),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
    public function dashboard(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }

            // Récupérer le wallet de l'utilisateur
            $wallet = $user->wallet()->first();

            if (!$wallet) {
                return $this->errorResponse('Portefeuille non trouvé', 404);
            }

            // Récupérer les transactions de l'utilisateur (dernières 10)
            $transactions = Transaction::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'type', 'amount', 'status', 'created_at', 'sender_id', 'receiver_id']);

            // Formater les données
            $data = [
                'user' => $user->only(['id', 'phone_number', 'first_name', 'last_name', 'email', 'kyc_status', 'status']),
                'compte' => [
                    'id' => $wallet->id,
                    'account_number' => $wallet->account_number,
                    'balance' => (float) $wallet->balance,
                    'currency' => $wallet->currency,
                    'qr_code' => $wallet->qr_code,
                    'status' => $wallet->status,
                ],
                'transactions' => $transactions->map(function ($transaction) use ($user) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'amount' => (float) $transaction->amount,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at,
                        'direction' => $transaction->sender_id === $user->id ? 'sent' : 'received',
                    ];
                }),
            ];

            return $this->successResponse($data, 'Tableau de bord chargé avec succès', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du chargement du tableau de bord: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/comptes/{id}/solde",
     *     summary="Obtenir le solde d'un compte par ID",
     *     tags={"Compte"},
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
     *         description="Solde obtenu avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solde du compte"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="account_number", type="string"),
     *                 @OA\Property(property="balance", type="number", format="float"),
     *                 @OA\Property(property="currency", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
    public function getSolde($id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }

            // Récupérer le wallet
            $wallet = $user->wallet()->where('id', $id)->first();

            if (!$wallet) {
                return $this->errorResponse('Compte non trouvé', 404);
            }

            $data = [
                'id' => $wallet->id,
                'account_number' => $wallet->account_number,
                'balance' => (float) $wallet->balance,
                'currency' => $wallet->currency,
            ];

            return $this->successResponse($data, 'Solde du compte', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération du solde: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/compte/{id}/depot",
     *     summary="Effectuer un dépôt sur un compte",
     *     tags={"Compte"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du wallet/compte",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", example=5000.00),
     *             @OA\Property(property="description", type="string", example="Dépôt initial", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dépôt effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dépôt effectué avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="new_balance", type="number", format="float"),
     *                 @OA\Property(property="transaction_id", type="string", format="uuid"),
     *                 @OA\Property(property="amount", type="number", format="float")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Montant invalide ou manquant"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
    public function depot(Request $request, $id): JsonResponse
    {
        try {
            // Valider les données
            $validated = $request->validate([
                'amount' => 'required|numeric|min:100',
                'description' => 'nullable|string|max:255',
            ]);

            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }

            // Récupérer le wallet
            $wallet = $user->wallet()->where('id', $id)->first();

            if (!$wallet) {
                return $this->errorResponse('Compte non trouvé', 404);
            }

            // Effectuer la transaction
            return DB::transaction(function () use ($wallet, $user, $validated) {
                // Augmenter le solde
                $wallet->increment('balance', $validated['amount']);

                // Créer la transaction
                $transaction = Transaction::create([
                    'sender_wallet_id' => null,
                    'receiver_wallet_id' => $wallet->id,
                    'receiver_id' => $user->id,
                    'amount' => $validated['amount'],
                    'fees' => 0,
                    'type' => Transaction::TYPE_DEPOSIT,
                    'status' => Transaction::STATUS_COMPLETED,
                    'reference' => 'DEP' . time(),
                    'description' => $validated['description'] ?? 'Dépôt d\'argent',
                ]);

                // Recharger le wallet pour avoir le nouveau solde
                $wallet->refresh();

                return $this->successResponse([
                    'new_balance' => (float) $wallet->balance,
                    'transaction_id' => $transaction->id,
                    'amount' => (float) $validated['amount'],
                ], 'Dépôt effectué avec succès', 200);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Erreur de validation: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du dépôt: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/compte/{id}/payment",
     *     summary="Effectuer un paiement depuis un compte",
     *     tags={"Compte"},
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
     *             required={"amount", "recipient_identifier", "payment_type"},
     *             @OA\Property(property="amount", type="number", format="float", example=1000.00),
     *             @OA\Property(property="recipient_identifier", type="string", example="1234567890"),
     *             @OA\Property(property="payment_type", type="string", enum={"phone_number", "merchant_code"}, example="phone_number"),
     *             @OA\Property(property="description", type="string", example="Paiement de facture", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Paiement effectué avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="new_balance", type="number", format="float"),
     *                 @OA\Property(property="transaction_id", type="string", format="uuid"),
     *                 @OA\Property(property="amount", type="number", format="float")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation ou fonds insuffisants"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte émetteur ou destinataire non trouvé"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur"
     *     )
     * )
     */
    public function payment(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:100',
                'recipient_identifier' => 'required|string',
                'payment_type' => 'required|in:phone_number,merchant_code',
                'description' => 'nullable|string|max:255',
            ]);

            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }

            $senderWallet = $user->wallet()->where('id', $id)->first();

            if (!$senderWallet) {
                return $this->errorResponse('Compte émetteur non trouvé', 404);
            }

            if ($senderWallet->balance < $validated['amount']) {
                return $this->errorResponse('Fonds insuffisants', 422);
            }

            return DB::transaction(function () use ($senderWallet, $user, $validated) {
                $receiverWallet = null;
                $receiverUser = null;

                if ($validated['payment_type'] === 'phone_number') {
                    $receiverUser = User::where('phone_number', $validated['recipient_identifier'])->first();
                    if ($receiverUser) {
                        $receiverWallet = $receiverUser->wallet()->first();
                    }
                } elseif ($validated['payment_type'] === 'merchant_code') {
                    // Logique pour trouver le marchand et son wallet
                    // Supposons que le code marchand est lié à un utilisateur ou un wallet
                    // Pour cet exemple, nous allons simuler la recherche d'un utilisateur par un code marchand
                    // Dans un cas réel, il faudrait une table `merchants` avec un `merchant_code`
                    $receiverUser = User::whereHas('merchant', function ($query) use ($validated) {
                        $query->where('merchant_code', $validated['recipient_identifier']);
                    })->first();

                    if ($receiverUser) {
                        $receiverWallet = $receiverUser->wallet()->first();
                    }
                }

                if (!$receiverWallet) {
                    return $this->errorResponse('Destinataire non trouvé', 404);
                }

                $senderWallet->decrement('balance', $validated['amount']);
                $receiverWallet->increment('balance', $validated['amount']);

                $transaction = Transaction::create([
                    'sender_wallet_id' => $senderWallet->id,
                    'receiver_wallet_id' => $receiverWallet->id,
                    'sender_id' => $user->id,
                    'receiver_id' => $receiverUser ? $receiverUser->id : null,
                    'amount' => $validated['amount'],
                    'fees' => 0,
                    'type' => Transaction::TYPE_PAYMENT,
                    'status' => Transaction::STATUS_COMPLETED,
                    'reference' => 'PAY' . time(),
                    'description' => $validated['description'] ?? 'Paiement',
                ]);

                $senderWallet->refresh();

                return $this->successResponse([
                    'new_balance' => (float) $senderWallet->balance,
                    'transaction_id' => $transaction->id,
                    'amount' => (float) $validated['amount'],
                ], 'Paiement effectué avec succès', 200);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Erreur de validation: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du paiement: ' . $e->getMessage(), 500);
        }
    }

    public function getTransactions(Request $request, $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }

            $wallet = $user->wallet()->where('id', $id)->first();

            if (!$wallet) {
                return $this->errorResponse('Compte non trouvé', 404);
            }

            $transactionsQuery = Transaction::where(function ($query) use ($wallet) {
                $query->where('sender_wallet_id', $wallet->id)
                      ->orWhere('receiver_wallet_id', $wallet->id);
            });

            if ($request->has('type')) {
                $transactionsQuery->where('type', $request->input('type'));
            }

            if ($request->has('status')) {
                $transactionsQuery->where('status', $request->input('status'));
            }

            $transactions = $transactionsQuery->orderBy('created_at', 'desc')->get();

            $formattedTransactions = $transactions->map(function ($transaction) use ($wallet) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'reference' => $transaction->reference,
                    'created_at' => $transaction->created_at,
                    'direction' => $transaction->sender_wallet_id === $wallet->id ? 'sent' : 'received',
                ];
            });

            return $this->successResponse($formattedTransactions, 'Historique des transactions chargé avec succès', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du chargement des transactions: ' . $e->getMessage(), 500);
        }
    }
}
