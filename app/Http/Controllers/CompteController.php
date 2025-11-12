<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\CompteService;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     *     path="/register",
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
     *     path="/verify-otp",
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
                $request->otp_code,
                $request->password
            );

            return $this->successResponse($user, 'Compte vérifié avec succès.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
