<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtiene la API Key de la solicitud
        $clientApiKey = $request->header('x-api-key');

        // Valida que la API Key de la solicitud coincida con la de la configuración
        if ($clientApiKey !== config('app.api_key')) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        return $next($request);
    }
}
