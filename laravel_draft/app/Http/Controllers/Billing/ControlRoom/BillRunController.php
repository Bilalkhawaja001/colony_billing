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
        $month = $request->input('month_cycle');
        $methodCode = $request->input('method_code', 'ATTENDANCE_PRORATED');

        $ctx = app(\App\Services\Billing\ControlRoom\ReadinessService::class)->resolveCycleContext($month);
        $preview = (new \App\Services\BillingEngine\Engine())->preview(
            $methodCode,
            $ctx['cycle_start_date'],
            $ctx['cycle_end_date'],
            (float) ($ctx['electric_rate'] ?? 0)
        );

        return view('billing_control.result', [
            'pageTitle' => 'Preview Result — '.($preview['method_label'] ?? $methodCode),
            'run' => 'PREVIEW',
            'dryRun' => $preview,
            'rows' => $preview['rows'] ?? [],
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
    public function voidRegenerate(Request $request, BillRunGenerateService $generator)
    {
        if (!$request->boolean('confirm_regenerate')) {
            return back()->with('error', 'Please tick the confirmation box to void and regenerate.');
        }
        $monthCycle = $request->input('month_cycle');
        $methodCode = $request->input('method_code', 'OCCUPIED_ROOM_EQUAL_SPLIT');
        $reason     = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            return back()->with('error', 'A reason is required before voiding an official bill run.');
        }
        $role = optional($request->user())->role ?? 'SUPER_ADMIN';

        $run = \App\Models\BillRun::where('month_cycle', $monthCycle)
            ->where('status', \App\Services\Billing\V2\BillRunStateMachine::GENERATED)
            ->orderByDesc('id')->first();
        if (!$run) {
            return back()->with('error', 'No GENERATED run found for '.$monthCycle.'.');
        }

        try {
            app(\App\Services\Billing\V2\BillRunGateService::class)
                ->transition($run->id, 'void', $role, optional($request->user())->id, $reason);
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not void the run: '.$e->getMessage());
        }

        \Illuminate\Support\Facades\DB::table('electric_v1_output_employee_final')
            ->where('cycle_start_date', $run->cycle_start_date)
            ->where('cycle_end_date', $run->cycle_end_date)->delete();
        \Illuminate\Support\Facades\DB::table('electric_v1_output_employee_unit_drilldown')
            ->where('cycle_start_date', $run->cycle_start_date)
            ->where('cycle_end_date', $run->cycle_end_date)->delete();

        $result = $generator->generate($monthCycle, optional($request->user())->id, $role, $methodCode);
        if (($result['status'] ?? '') !== 'ok') {
            return back()->with('error', 'Voided run '.$run->run_uuid.', but regeneration failed: '.($result['reason'] ?? 'unknown'));
        }
        return redirect()->route('billing.control.export', ['month_cycle'=>$monthCycle])
            ->with('success', 'Voided '.$run->run_uuid.' and regenerated. New Bill Reference: '.$result['bill_reference']);
    }

    public function generate(Request $request, BillRunGenerateService $generator)
    {
        if (!$request->boolean('confirm_official')) {
            return back()->with('error','Please confirm you understand this creates official bill records.');
        }
        $monthCycle  = $request->input('month_cycle');
        $actorUserId = optional($request->user())->id;
        $role        = optional($request->user())->role ?? null;

        $methodCode  = $request->input('method_code', 'ATTENDANCE_PRORATED');
        // Phase 3 gate: har data issue ka decision zaroori
        $readiness = app(\App\Services\Billing\ControlRoom\ReadinessService::class)->summary($monthCycle);
        $pending = (int) ($readiness['pendingDecisions'] ?? 0);
        $issueCount = (int) ($readiness['dataIssueCount'] ?? 0);

        if ($pending > 0 && !$request->boolean('continue_anyway')) {
            return back()->with('error', $pending.' data issue(s) still need a decision. Review them on the Readiness page, or tick "Continue Anyway".');
        }


        $result = $generator->generate($monthCycle, $actorUserId, $role, $methodCode);

        if (($result['status'] ?? '') !== 'ok') {
            return back()->with('error', $result['reason'] ?? 'Generation blocked.');
        }
        return redirect()->route('billing.control.export', ['month_cycle'=>$monthCycle])
            ->with('success','Official bills generated. Bill Reference: '.$result['bill_reference']);
    }
}
