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
     *     path="/v1/auth/login",
     *     summary="Connexion utilisateur",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number", "password"},
     *             @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="a1b2c3d4-e5f6-7890-1234-567890abcdef"),
     *                 @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="kyc_status", type="string", example="pending"),
     *                 @OA\Property(property="biometrics_active", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides"
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

}
