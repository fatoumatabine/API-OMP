<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    /**
      * @OA\Post(
      *     path="/api/auth/login",
      *     summary="Connexion utilisateur",
      *     description="Authentifier un utilisateur avec son numéro de téléphone et son mot de passe. Retourne un JWT token.",
      *     tags={"Authentication"},
      *     @OA\RequestBody(
      *         required=true,
      *         description="Identifiants de l'utilisateur",
      *         @OA\JsonContent(
      *             required={"phone_number", "password"},
      *             @OA\Property(property="phone_number", type="string", example="+22145678901", description="Numéro de téléphone au format international"),
      *             @OA\Property(property="password", type="string", format="password", example="password123", description="Mot de passe")
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="Connexion réussie",
      *         @OA\JsonContent(
      *             @OA\Property(property="success", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Connexion réussie"),
      *             @OA\Property(property="data", type="object",
      *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
      *                 @OA\Property(property="user", type="object",
      *                     @OA\Property(property="id", type="string", format="uuid", example="a1b2c3d4-e5f6-7890-1234-567890abcdef"),
      *                     @OA\Property(property="phone_number", type="string", example="+22145678901"),
      *                     @OA\Property(property="first_name", type="string", example="John"),
      *                     @OA\Property(property="last_name", type="string", example="Doe"),
      *                     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
      *                     @OA\Property(property="kyc_status", type="string", enum={"pending", "verified", "rejected"}),
      *                     @OA\Property(property="biometrics_active", type="boolean", example=false),
      *                     @OA\Property(property="balance", type="number", format="double", example=10000),
      *                     @OA\Property(property="created_at", type="string", format="date-time"),
      *                     @OA\Property(property="updated_at", type="string", format="date-time")
      *                 )
      *             )
      *         )
      *     ),
      *     @OA\Response(
      *         response=401,
      *         description="Identifiants invalides",
      *         @OA\JsonContent(
      *             @OA\Property(property="success", type="boolean", example=false),
      *             @OA\Property(property="message", type="string", example="Identifiants invalides")
      *         )
      *     )
      * )
      */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('phone_number', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $this->successResponse([
            'token' => $token,
            'user' => $user
        ], 'Connexion réussie');
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh-token",
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
     *     path="/auth/change-pin",
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
    public function changePin(Request $request): JsonResponse
    {
        $request->validate([
            'old_pin' => 'required|string|min:4|max:4',
            'new_pin' => 'required|string|min:4|max:4',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->pin_code || !Hash::check($request->old_pin, $user->pin_code)) {
            return $this->errorResponse('Ancien code PIN invalide', 401);
        }

        $user->pin_code = Hash::make($request->new_pin);
        $user->save();

        return $this->successResponse(null, 'Code PIN changé avec succès');
    }

    /**
     * @OA\Post(
     *     path="/auth/create-pin",
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
    public function createPin(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'pin' => 'required|string|min:4|max:4',
            ]);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->pin_code) {
                return $this->errorResponse('Code PIN déjà existant', 400);
            }

            $user->pin_code = Hash::make($request->pin);
            $user->save();

            return $this->successResponse(null, 'Code PIN créé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création du PIN: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/verify-otp",
     *     summary="Vérifier un code OTP",
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
     *         description="OTP vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP vérifié avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP invalide ou expiré"
     *     )
     * )
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone_number' => 'required|string',
                'otp' => 'required|string|min:6|max:6',
            ]);

            // Logique de vérification OTP (à implémenter)
            // Pour l'instant, on simule une vérification réussie
            if ($request->otp === '123456') { // Exemple simple
                return $this->successResponse(null, 'OTP vérifié avec succès');
            }

            return $this->errorResponse('OTP invalide ou expiré', 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la vérification OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/resend-otp",
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
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        // Logique de renvoi OTP (à implémenter)
        // Pour l'instant, on simule un renvoi réussi
        return $this->successResponse(null, 'OTP renvoyé avec succès');
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
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
