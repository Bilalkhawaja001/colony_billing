<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MonthGuardShell
{
    public function handle(Request $request, Closure $next)
    {
        // Draft-only shell gate. No billing/month domain logic executed here.
        $path = '/'.trim($request->path(), '/');
        $method = strtoupper($request->method());

        if (!in_array($method, ['POST','PUT','PATCH','DELETE'], true)) {
            return $next($request);
        }

        $protected = config('month_guard.protected_write_paths', []);
        $exceptions = config('month_guard.intentional_exceptions', []);

        if (in_array($path, $protected, true) && !in_array($path, $exceptions, true)) {
            return response()->json([
                'status' => 'error',
                'error' => 'month guard shell blocked write',
                'note' => 'domain month guard not implemented in LIMITED GO'
            ], 423);
        }

        return $next($request);
    }
}
