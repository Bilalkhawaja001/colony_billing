<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShellPathRbac
{
    public function handle(Request $request, Closure $next)
    {
        $role = session('role');
        $path = '/'.trim($request->path(), '/');

        $map = config('rbac_shell.path_roles', []);
        if (isset($map[$path])) {
            if (!$role || !in_array($role, $map[$path], true)) {
                return $request->is('api/*')
                    ? response()->json(['status' => 'error', 'error' => 'forbidden'], 403)
                    : abort(403);
            }
        }

        // Flask parity: VIEWER cannot mutate API endpoints.
        if ($request->is('api/*') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $role === 'VIEWER') {
            return response()->json(['status' => 'error', 'error' => 'forbidden'], 403);
        }

        return $next($request);
    }
}
