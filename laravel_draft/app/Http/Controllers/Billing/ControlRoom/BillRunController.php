<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\GenerateDryRunService;
use App\Services\Billing\ControlRoom\ReadinessService;
use App\Services\Billing\ControlRoom\RealGenerateSafetyAuditService;
use Illuminate\Http\Request;
use App\Services\Billing\V2\BillRunGenerateService;

class BillRunController extends Controller
{
    public function index(Request $request, ReadinessService $readiness, RealGenerateSafetyAuditService $safetyAudit)
    {
        $monthCycle = $request->query('month_cycle');

        $readinessSummary = $readiness->summary($monthCycle, false);

        return view('billing_control.generate', [
            'pageTitle' => 'Preview Bills',
            'readiness' => $readinessSummary,
            'safetyAudit' => $safetyAudit->audit($monthCycle),
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
            'message' => 'Phase 1E safety audit only. Real queue status requires Phase 1F approval.',
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
    public function generate(Request $request, BillRunGenerateService $generator)
    {
        if (!$request->boolean('confirm_official')) {
            return back()->with('error','Please confirm you understand this creates official bill records.');
        }
        $monthCycle  = $request->input('month_cycle');
        $actorUserId = optional($request->user())->id;
        $role        = optional($request->user())->role ?? null;

        $result = $generator->generate($monthCycle, $actorUserId, $role);

        if (($result['status'] ?? '') !== 'ok') {
            return back()->with('error', $result['reason'] ?? 'Generation blocked.');
        }
        return redirect()->route('billing.control.export', ['month_cycle'=>$monthCycle])
            ->with('success','Official bills generated. Bill Reference: '.$result['bill_reference']);
    }
}
