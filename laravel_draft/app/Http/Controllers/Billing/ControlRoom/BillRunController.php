<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\ReadinessService;
use Illuminate\Http\Request;

class BillRunController extends Controller
{
    public function index(Request $request, ReadinessService $readiness)
    {
        return view('billing_control.generate', [
            'pageTitle' => 'Generate Bill',
            'readiness' => $readiness->summary($request->query('month_cycle')),
        ]);
    }

    public function store(Request $request, ReadinessService $readiness)
    {
        return back()->withErrors([
            'generate' => 'Generate blocked. Phase 1C only; real queued job requires Phase 1D approval.',
        ]);
    }

    public function status(string $run)
    {
        return response()->json([
            'run' => $run,
            'status' => 'SCAFFOLD_ONLY',
            'message' => 'Real queued job status pending Phase 1D.',
        ]);
    }

    public function show(string $run)
    {
        return view('billing_control.result', [
            'pageTitle' => 'Bill Result',
            'run' => $run,
            'rows' => [],
        ]);
    }

    public function row(string $run, string $row)
    {
        return response()->json([
            'run' => $run,
            'row' => $row,
            'status' => 'SCAFFOLD_ONLY',
        ]);
    }
}
