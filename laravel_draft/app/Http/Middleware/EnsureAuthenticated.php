<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user_id')) {
            return ($request->is('api/*') || $request->expectsJson())
                ? response()->json(['status' => 'error', 'error' => 'authentication required'], 401)
                : redirect('https://nodesky.pk/login');
        }

        return $next($request);
    }
}
