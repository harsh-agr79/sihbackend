<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-api-key');

        // Check if the API key matches the expected value
        if ($request->header('x-api-key') !== getenv('APP_API_PRIVATE_KEY')) {
        return response()->json(['message' => 'Unauthorized'], 401);

        }

        return $next($request);
    }
}
