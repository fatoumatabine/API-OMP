<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log uniquement pour les opérations de création (POST)
        if ($request->isMethod('POST')) {
            $operationName = $this->getOperationName($request);
            $resource = $this->getResource($request);

            Log::info('Opération de création', [
                'date_heure' => now()->toDateTimeString(),
                'host' => $request->ip(),
                'nom_operation' => $operationName,
                'ressource' => $resource,
                'request_body' => $request->all(),
                'response_status' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
            ]);
        }

        return $response;
    }

    protected function getOperationName(Request $request): string
    {
        // Exemple simple, à adapter selon la structure de vos routes
        if ($request->routeIs('register')) { // Assurez-vous que 'register' est le nom de votre route
            return 'Création de compte utilisateur';
        }
        // Ajoutez d'autres conditions pour d'autres opérations de création
        return 'Opération de création inconnue';
    }

    protected function getResource(Request $request): string
    {
        // Exemple simple, à adapter
        if ($request->routeIs('register')) {
            return 'Utilisateur';
        }
        return 'Ressource inconnue';
    }
}
