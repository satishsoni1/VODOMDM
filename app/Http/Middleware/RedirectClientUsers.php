<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectClientUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->isClient()) {
            return redirect()->route('client.dashboard');
        }
        return $next($request);
    }
}
