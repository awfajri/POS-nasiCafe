<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Stub Fase 0: Implementasi penuh dikerjakan oleh Hafiz di Fase 1.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        return $next($request);
    }
}
