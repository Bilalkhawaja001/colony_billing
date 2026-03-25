<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $force = (int) session('force_change_password', 0) === 1;

        if (!$force) {
            return $next($request);
        }

        $allowed = [
            'ui/profile',
            'api/profile/change-password',
            'logout',
        ];

        $path = trim($request->path(), '/');
        if (in_array($path, $allowed, true) || str_starts_with($path, 'static/')) {
            return $next($request);
        }

        return $request->is('api/*')
            ? response()->json(['status' => 'error', 'error' => 'password change required'], 403)
            : redirect('/ui/profile');
    }
}
