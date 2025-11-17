<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePinRequest;
use App\Http\Requests\CreatePinRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LoginVerifyOtpRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints pour l'authentification"
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    private AuditLogService $auditService;

    public function __construct(AuditLogService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Initier la connexion avec OTP",
     *     description="Authentifier un utilisateur avec son numéro de téléphone. Un code OTP sera envoyé par email.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Numéro de téléphone de l'utilisateur",
     *         @OA\JsonContent(
     *             required={"phone_number"},
     *             @OA\Property(property="phone_number", type="string", example="+22145678901", description="Numéro de téléphone au format international")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code OTP envoyé à votre email"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string", format="uuid", example="a1b2c3d4-e5f6-7890-1234-567890abcdef"),
     *                 @OA\Property(property="phone_number", type="string", example="+22145678901"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            fwrite(STDERR, "[AUTH] Login attempt for phone: " . $request->phone_number . "\n");
            
            $user = User::where('phone_number', $request->phone_number)->first();
            
            if (!$user) {
                fwrite(STDERR, "[AUTH] User not found: " . $request->phone_number . "\n");
                return $this->errorResponse('Utilisateur non trouvé', 404);
            }

            fwrite(STDERR, "[AUTH] User found: " . $user->email . "\n");

            // Générer et envoyer OTP
            $otpService = app(OtpService::class);
            fwrite(STDERR, "[AUTH] Sending OTP to " . $user->email . "\n");
            $otpService->generateAndSendOtp($user);
            
            fwrite(STDERR, "[AUTH] OTP sent successfully\n");

            return $this->successResponse([
                'user_id' => $user->id,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
            ], 'Code OTP envoyé à votre email', 200);
        } catch (\Exception $e) {
            fwrite(STDERR, "[AUTH ERROR] " . $e->getMessage() . " | Trace: " . $e->getTraceAsString() . "\n");
            return $this->errorResponse('Erreur lors de la connexion: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh-token",
     *     summary="Rafraîchir le token API de l'utilisateur authentifié",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token rafraîchi avec succès"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function refreshToken(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $this->successResponse(['token' => $token], 'Token rafraîchi avec succès');
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->errorResponse('Impossible de rafraîchir le token', 401);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du rafraîchissement du token', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/change-pin",
     *     summary="Changer le code PIN de l'utilisateur authentifié",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_pin", "new_pin"},
     *             @OA\Property(property="old_pin", type="string", example="1234"),
     *             @OA\Property(property="new_pin", type="string", example="5678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code PIN changé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Code PIN changé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ancien code PIN invalide"
     *     )
     * )
     */
    public function changePin(ChangePinRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->pin_code || !Hash::check($request->old_pin, $user->pin_code)) {
            $this->auditService->logPinChange($user, false, 'Invalid old PIN');
            return $this->errorResponse('Ancien code PIN invalide', 401);
        }

        $user->pin_code = Hash::make($request->new_pin);
        $user->save();

        $this->auditService->logPinChange($user, true);

        return $this->successResponse(null, 'Code PIN changé avec succès');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/create-pin",
     *     summary="Créer un code PIN pour l'utilisateur authentifié",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"pin"},
     *             @OA\Property(property="pin", type="string", example="1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code PIN créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Code PIN créé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code PIN déjà existant"
     *     )
     * )
     */
    public function createPin(CreatePinRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->pin_code) {
                $this->auditService->logPinChange($user, false, 'PIN already exists');
                return $this->errorResponse('Code PIN déjà existant', 400);
            }

            $user->pin_code = Hash::make($request->pin);
            $user->save();

            $this->auditService->logPinChange($user, true);

            return $this->successResponse(null, 'Code PIN créé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création du PIN: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/verify-otp",
     *     summary="Vérifier le code OTP et obtenir le token JWT",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number", "otp"},
     *             @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP vérifié avec succès - Token JWT obtenu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP vérifié avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP invalide ou expiré"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     )
     * )
     */
    public function verifyOtp(LoginVerifyOtpRequest $request): JsonResponse
    {
        try {
            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user) {
                $this->auditService->logLoginAttempt($request->phone_number, false, 'User not found');
                return $this->errorResponse('Utilisateur non trouvé', 404);
            }

            $otpService = app(OtpService::class);

            if (!$otpService->verifyOtp($user, $request->otp)) {
                $this->auditService->logOtpVerification($user, false, 'Invalid or expired OTP');
                return $this->errorResponse('OTP invalide ou expiré', 400);
            }

            // Nettoyer l'OTP
            $otpService->clearOtp($user);

            // Log OTP verification success
            $this->auditService->logOtpVerification($user, true);

            // Générer le JWT token
            $token = JWTAuth::fromUser($user);

            // Log login success
            $this->auditService->logLoginAttempt($user->phone_number, true);

            return $this->successResponse([
                'token' => $token,
            ], 'OTP vérifié avec succès', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la vérification OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/resend-otp",
     *     summary="Renvoyer un code OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number"},
     *             @OA\Property(property="phone_number", type="string", example="+22245678901")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP renvoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP renvoyé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Impossible de renvoyer l'OTP"
     *     )
     * )
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        try {
            $user = User::where('phone_number', $request->phone_number)->first();
            
            if (!$user) {
                return $this->errorResponse('Utilisateur non trouvé', 404);
            }

            // Générer et envoyer OTP
            $otpService = app(OtpService::class);
            $otpService->generateAndSendOtp($user);

            return $this->successResponse(null, 'OTP renvoyé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors du renvoi de l\'OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Déconnexion de l'utilisateur (invalide le token)",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->successResponse(null, 'Déconnexion réussie');
    }
}
