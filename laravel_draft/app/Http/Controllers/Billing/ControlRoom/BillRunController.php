<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\ReadinessService;
use Illuminate\Http\Request;

class BillRunController extends Controller
{
    public function index(ReadinessService $readiness)
    {
        return view('billing_control.generate', [
            'pageTitle' => 'Generate Bill',
            'readiness' => $readiness->summary(),
        ]);
    }

    public function store(Request $request, ReadinessService $readiness)
    {
        return back()->withErrors([
            'generate' => 'Generate blocked. Phase 1A scaffold only; real queued job not wired yet.',
        ]);
    }

    public function status(string $run)
    {
        return response()->json([
            'run' => $run,
            'status' => 'SCAFFOLD_ONLY',
            'message' => 'Real queued job status pending Phase 2.',
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
