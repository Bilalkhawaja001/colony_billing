<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MonthGuardShell
{
    private function isLocked(Request $request): bool
    {
        $header = (string) config('month_guard.state.header_override', 'X-Month-Locked');
        $hv = $request->headers->get($header);
        if ($hv !== null) {
            return in_array(strtolower((string)$hv), ['1', 'true', 'yes', 'locked'], true);
        }

        $sessionKey = (string) config('month_guard.state.session_key', 'month_guard_locked');
        if (session()->has($sessionKey)) {
            return (bool) session($sessionKey);
        }

        return (bool) config('month_guard.state.default_locked', true);
    }

    public function handle(Request $request, Closure $next)
    {
        $path = '/'.trim($request->path(), '/');
        $method = strtoupper($request->method());

        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $protected = config('month_guard.protected_write_paths', []);
        if (!in_array($path, $protected, true)) {
            return $next($request);
        }

        $exceptions = config('month_guard.intentional_exceptions', []);
        if (in_array($path, $exceptions, true)) {
            return $next($request);
        }

        if (!$this->isLocked($request)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'error' => 'month locked',
            'guard' => 'month.guard.shell',
            'note' => 'domain month guard not implemented in LIMITED GO',
        ], 423);
    }
}
