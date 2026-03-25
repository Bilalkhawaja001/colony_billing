<?php

namespace App\Http\Middleware;

use App\Services\Month\MonthStateService;
use Closure;
use Illuminate\Http\Request;

class MonthGuardShell
{
    public function __construct(private readonly MonthStateService $monthState)
    {
    }

    private function resolveMonthCycle(Request $request): ?string
    {
        $month = trim((string)($request->input('month_cycle') ?? $request->query('month_cycle') ?? ''));
        if ($month !== '') {
            return $month;
        }

        $runId = (int)($request->input('run_id') ?? 0);
        if ($runId > 0) {
            return $this->monthState->monthFromRunId($runId);
        }

        return null;
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

        $monthCycle = $this->resolveMonthCycle($request);
        if (!$monthCycle) {
            // No resolvable month in shell mode; defer to endpoint validation/guards.
            return $next($request);
        }

        if (!$this->monthState->isLocked($monthCycle)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'error' => "month_cycle {$monthCycle} is LOCKED; post lock edits are blocked",
            'guard' => 'month.guard.domain',
        ], 409);
    }
}
