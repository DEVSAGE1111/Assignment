<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = env('API_SECRET_TOKEN', 'formify_secret_token_12345');
        $providedToken = $request->header('X-API-Key');

        if (empty($providedToken) || $providedToken !== $configuredToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing X-API-Key token.',
            ], 401);
        }

        return $next($request);
    }
}
