<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleGate
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $role = session('role');

        if (!$role) {
            return ($request->is('api/*') || $request->expectsJson())
                ? response()->json(['status' => 'error', 'error' => 'authentication required'], 401)
                : redirect('/login');
        }

        if (!in_array($role, $roles, true)) {
            return ($request->is('api/*') || $request->expectsJson())
                ? response()->json(['status' => 'error', 'error' => 'forbidden'], 403)
                : abort(403);
        }

        return $next($request);
    }
}
