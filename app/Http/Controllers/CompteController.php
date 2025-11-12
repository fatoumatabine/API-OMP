<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\CompteService;
use App\Http\Traits\ApiResponseTrait;
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
     *     summary="Enregistrer un nouvel utilisateur",
     *     tags={"Compte"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_number", "first_name", "last_name", "email", "password", "password_confirmation", "pin_code", "cni_number"},
     *             @OA\Property(property="phone_number", type="string", example="+22245678901"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     *             @OA\Property(property="pin_code", type="string", example="1234"),
     *             @OA\Property(property="cni_number", type="string", example="1234567890ABC"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur enregistré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur enregistré avec succès."),
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
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->compteService->createCompte($request->validated());
            return $this->successResponse($user, 'Utilisateur enregistré avec succès.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de l\'enregistrement de l\'utilisateur: ' . $e->getMessage(), 500);
        }
    }
}
