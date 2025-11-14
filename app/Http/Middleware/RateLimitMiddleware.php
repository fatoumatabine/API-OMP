<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getKey($request);
        $limit = $this->getLimit($request);
        $decay = $this->getDecay($request);

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de requêtes. Veuillez réessayer plus tard.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $this->limiter->hit($key, $decay);

        return $next($request);
    }

    protected function getKey(Request $request): string
    {
        return 'rate_limit:' . ($request->user()?->id ?? $request->ip());
    }

    protected function getLimit(Request $request): int
    {
        // Limite différente selon l'endpoint
        return match (true) {
            $request->is('api/auth/*') => 5, // 5 tentatives par minute pour l'auth
            $request->is('api/transactions/*') => 20, // 20 requêtes par minute pour les transactions
            $request->is('api/wallet/*') => 20, // 20 requêtes par minute pour le wallet
            default => 60, // 60 requêtes par minute par défaut
        };
    }

    protected function getDecay(Request $request): int
    {
        return 60; // 1 minute
    }
}
