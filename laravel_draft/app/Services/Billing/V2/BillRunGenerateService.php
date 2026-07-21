<?php

namespace App\Services\Billing\V2;

use App\Models\BillRun;
use App\Services\Billing\ControlRoom\ReadinessService;
use App\Services\ElectricV1\OrchestrationService;
use Illuminate\Support\Str;

class BillRunGenerateService
{
    public function __construct(
        private readonly ReadinessService $readiness,
        private readonly BillRunPreflightService $preflight,
        private readonly BillRunGateService $gate,
    ) {}

    public function generate(?string $monthCycle, ?int $actorUserId = null, ?string $role = null, ?string $methodCode = null): array
    {
        // ===== PHASE 1: resolve + guard + draft + preflight + preview_ready =====
        $ctx = $this->readiness->resolveCycleContext($monthCycle);
        $month      = $ctx['month_cycle'];
        $billMonth  = $ctx['billing_month_date'];
        $cycleStart = $ctx['cycle_start_date'];
        $cycleEnd   = $ctx['cycle_end_date'];
        $rate       = $ctx['electric_rate'];

        if (!$month || !$billMonth || !$cycleStart || !$cycleEnd) {
            return ['status'=>'blocked','reason'=>'Cycle context incomplete. Final generation setup required.'];
        }
        if (!$rate || (float)$rate <= 0) {
            return ['status'=>'blocked','reason'=>'Electricity rate missing for this month.'];
        }

        $billType  = 'electric_v1';
        $scopeType = 'FULL_ELIGIBLE';
        $scopeHash = BillRunStateMachine::scopeHash([], ['scope_type'=>$scopeType]);
        $periodKey = BillRunStateMachine::periodKey($month, $cycleStart, $cycleEnd);
        $committedKey = BillRunStateMachine::committedScopeKey($periodKey, $billType, $scopeHash, BillRunStateMachine::GENERATED);

        // PRE-ENGINE DUPLICATE GUARD (overwrite protection)
        $dup = BillRun::query()
            ->where('committed_scope_key', $committedKey)
            ->whereIn('status', BillRunStateMachine::COMMITTED)
            ->first();
        if ($dup) {
            return ['status'=>'blocked',
                    'reason'=>'Official bills already generated for this month. Bill Reference: '.$dup->run_uuid,
                    'bill_reference'=>$dup->run_uuid];
        }

        // DRAFT run header
        $run = BillRun::query()->firstOrNew([
            'month_cycle'=>$month, 'bill_type'=>$billType, 'scope_hash'=>$scopeHash,
        ]);
        if (BillRunStateMachine::isCommitted($run->status)) {
            return ['status'=>'blocked',
                    'reason'=>'A committed run already exists. Bill Reference: '.$run->run_uuid,
                    'bill_reference'=>$run->run_uuid];
        }
        if (!$run->exists) {
            $run->run_uuid   = (string) Str::uuid();
            $run->source     = 'v2';
            $run->scope_type = $scopeType;
            $run->status     = BillRunStateMachine::DRAFT;
            $run->created_by_user_id = $actorUserId;
        }
        $run->cycle_start_date = $cycleStart;
        $run->cycle_end_date   = $cycleEnd;
        $run->cycle_days       = $ctx['cycle_days'];
        $run->save();

        // preflight
        $pf  = $this->preflight->evaluate($run);
        $this->preflight->saveResult($run, $pf);
        $sum = $pf['summary'] ?? [];
        if (($sum['stop'] ?? 0) > 0 || ($sum['fail'] ?? 0) > 0) {
            return ['status'=>'blocked','reason'=>'Preflight checks failed. Fix data before generating.','preflight'=>$sum];
        }

        // DRAFT -> PREVIEW_READY
        try {
            $this->gate->transition($run->id, 'mark_preview_ready', $role, $actorUserId);
        } catch (\Throwable $e) {
            return ['status'=>'blocked','reason'=>'Cannot move to preview-ready: '.$e->getMessage()];
        }

        // ===== PHASE 2: engine + summary + mark_generated (Option D: NO outer wrapper) =====
        // re-check duplicate guard (race safety) before engine
        $dup2 = BillRun::query()
            ->where('committed_scope_key', $committedKey)
            ->whereIn('status', BillRunStateMachine::COMMITTED)
            ->first();
        if ($dup2) {
            return ['status'=>'blocked',
                    'reason'=>'Official bills already generated for this month. Bill Reference: '.$dup2->run_uuid,
                    'bill_reference'=>$dup2->run_uuid];
        }

        // engine run (own internal transaction)
        try {
            if ($methodCode && $methodCode !== 'ATTENDANCE_PRORATED') {
                $pv = (new \App\Services\BillingEngine\Engine())->preview($methodCode, $cycleStart, $cycleEnd, (float)$rate);
                if (!($pv['ok'] ?? false)) { return ['status'=>'blocked','reason'=>'Method not found: '.$methodCode]; }
                $engRunId = 'RUN-'.substr(md5(uniqid()),0,12);
                $w = (new \App\Services\BillingEngine\Support\OutputWriter())->write($pv, $engRunId);
                $er = ['run_id'=>$engRunId, 'final_output_rows'=>$w['final_rows'], 'drilldown_output_rows'=>$w['drill_rows'], 'exception_rows'=>count($pv['issues'] ?? [])];
            } else {
                $er = app(OrchestrationService::class)->run($billMonth, $cycleStart, $cycleEnd, (float)$rate);
            }
        } catch (\Throwable $e) {
            return ['status'=>'blocked','reason'=>'Generation failed during calculation: '.$e->getMessage()];
        }

        // summary_json (run_uuid = public ref, RUN-xxxx = internal)
        $run->method_code = $methodCode ?: 'ATTENDANCE_PRORATED';
        $exceptionCount = (int) ($er['exception_rows'] ?? 0);
        $run->summary_json = json_encode([
            'bill_reference'        => $run->run_uuid,
            'generated_with_exceptions' => $exceptionCount > 0,
            'exception_count'       => $exceptionCount,
            'electric_engine_run_id'=> $er['run_id'] ?? null,
            'engine'                => 'electric_v1',
            'final_rows'            => $er['final_output_rows'] ?? 0,
            'drilldown_rows'        => $er['drilldown_rows'] ?? 0,
            'processed_count'       => $er['processed_count'] ?? 0,
            'skipped_count'         => $er['skipped_count'] ?? 0,
            'exception_count'       => $er['exception_count'] ?? 0,
            'cycle_start_date'      => $cycleStart,
            'cycle_end_date'        => $cycleEnd,
        ], JSON_UNESCAPED_SLASHES);
        $run->save();

        // PREVIEW_READY -> GENERATED (own transaction: committed_key + dup-assert + audit)
        try {
            $this->gate->transition($run->id, 'mark_generated', $role, $actorUserId);
        } catch (\Throwable $e) {
            return ['status'=>'blocked',
                    'reason'=>'Engine output may exist but bill run was not marked GENERATED. Detail: '.$e->getMessage(),
                    'bill_reference'=>$run->run_uuid];
        }

        $run->refresh();
        return [
            'status'=>'ok',
            'bill_reference'=>$run->run_uuid,
            'summary'=>[
                'final_rows'    => $er['final_output_rows'] ?? 0,
                'drilldown_rows'=> $er['drilldown_rows'] ?? 0,
                'exceptions'    => $er['exception_count'] ?? 0,
            ],
        ];
    }
}
