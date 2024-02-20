<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class ApiKeyAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'API key is missing'], 401);
        }

        $user = User::where('api_key', $apiKey)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Attach the authenticated user to the request for later use
        $request->user = $user;

        return $next($request);
    }
}
