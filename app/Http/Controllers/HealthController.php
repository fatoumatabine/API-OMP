<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Health",
 *     description="Endpoints pour la santé de l'API"
 * )
 */
class HealthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/health",
     *     summary="Vérifier la santé de l'API",
     *     tags={"Health"},
     *     @OA\Response(
     *         response=200,
     *         description="API en bonne santé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="uptime", type="integer", description="Uptime en secondes"),
     *             @OA\Property(property="database", type="boolean", example=true),
     *             @OA\Property(property="version", type="string", example="1.0.1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Service indisponible"
     *     )
     * )
     */
    public function health(): JsonResponse
    {
        $status = 'ok';
        $databaseStatus = $this->checkDatabase();

        if (!$databaseStatus) {
            $status = 'degraded';
        }

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'uptime' => (int)(microtime(true) - LARAVEL_START),
            'database' => $databaseStatus,
            'version' => config('app.version', '1.0.1'),
        ], $databaseStatus ? 200 : 503);
    }

    /**
     * @OA\Get(
     *     path="/api/health/detailed",
     *     summary="Vérifier la santé détaillée de l'API",
     *     tags={"Health"},
     *     @OA\Response(
     *         response=200,
     *         description="Santé détaillée de l'API",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(property="checks", type="object",
     *                 @OA\Property(property="database", type="boolean"),
     *                 @OA\Property(property="cache", type="boolean"),
     *                 @OA\Property(property="disk", type="boolean")
     *             ),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function healthDetailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk' => $this->checkDisk(),
        ];

        $status = collect($checks)->every(fn($check) => $check === true) ? 'ok' : 'degraded';

        return response()->json([
            'status' => $status,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.1'),
        ], $status === 'ok' ? 200 : 503);
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache connection
     */
    private function checkCache(): bool
    {
        try {
            cache()->put('health_check', true, 1);
            $result = cache()->get('health_check') === true;
            cache()->forget('health_check');
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check disk space
     */
    private function checkDisk(): bool
    {
        try {
            $disk = disk_free_space(storage_path());
            return $disk !== false && $disk > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
