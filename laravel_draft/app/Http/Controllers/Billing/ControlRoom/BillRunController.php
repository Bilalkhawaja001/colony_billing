<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\GenerateDryRunService;
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

    public function store(Request $request, GenerateDryRunService $dryRunService)
    {
        $dryRun = $dryRunService->run($request->input('month_cycle'));

        return view('billing_control.result', [
            'pageTitle' => 'Generate Dry Run Result',
            'run' => $dryRun['dry_run_id'],
            'dryRun' => $dryRun,
            'rows' => [],
        ]);
    }

    public function status(string $run)
    {
        return response()->json([
            'run' => $run,
            'status' => 'DRY_RUN_ONLY',
            'message' => 'Phase 1D dry run only. Real queue status requires Phase 1E approval.',
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
            'status' => 'DRY_RUN_ONLY',
        ]);
    }
}
