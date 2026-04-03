<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
