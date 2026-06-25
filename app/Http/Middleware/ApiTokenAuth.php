<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.api_token');

        // Accept via Bearer header or ?api_token= query param
        $provided = null;
        $authHeader = $request->header('Authorization', '');
        if (str_starts_with($authHeader, 'Bearer ')) {
            $provided = substr($authHeader, 7);
        } elseif ($request->filled('api_token')) {
            $provided = $request->query('api_token');
        }

        if (! $expected || ! $provided || ! hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API token.',
            ], 401);
        }

        return $next($request);
    }
}
