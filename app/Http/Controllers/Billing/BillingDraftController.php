<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\BillingAdjustmentApproveRequest;
use App\Http\Requests\Billing\BillingAdjustmentCreateRequest;
use App\Http\Requests\Billing\BillingApproveRequest;
use App\Http\Requests\Billing\BillingFinalizeRequest;
use App\Http\Requests\Billing\BillingLockRequest;
use App\Http\Requests\Billing\BillingPrecheckRequest;
use App\Http\Requests\Billing\BillingRunRequest;
use App\Http\Requests\Billing\RatesApproveRequest;
use App\Http\Requests\Billing\RatesUpsertRequest;
use App\Http\Requests\Billing\ReconciliationReportRequest;
use App\Http\Requests\Billing\RecoveryPaymentRequest;
use App\Http\Requests\Billing\ReportMonthCycleRequest;
use App\Services\Billing\DraftBillingFlowService;

class BillingDraftController extends Controller
{
    public function __construct(private readonly DraftBillingFlowService $service)
    {
    }

    public function ratesUpsert(RatesUpsertRequest $request)
    {
        return response()->json($this->service->ratesUpsert($request->validated()));
    }

    public function ratesApprove(RatesApproveRequest $request)
    {
        return response()->json($this->service->ratesApprove($request->validated()));
    }

    public function run(BillingRunRequest $request)
    {
        return response()->json($this->service->run($request->validated()));
    }

    public function precheck(BillingPrecheckRequest $request)
    {
        return response()->json($this->service->precheck($request->validated()));
    }

    public function finalize(BillingFinalizeRequest $request)
    {
        $result = $this->service->finalize($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function lock(BillingLockRequest $request)
    {
        $result = $this->service->lock($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function approve(BillingApproveRequest $request)
    {
        $result = $this->service->approve($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function adjustmentCreate(BillingAdjustmentCreateRequest $request)
    {
        $result = $this->service->adjustmentCreate($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function adjustmentApprove(BillingAdjustmentApproveRequest $request)
    {
        $result = $this->service->adjustmentApprove($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function recoveryPayment(RecoveryPaymentRequest $request)
    {
        $result = $this->service->recoveryPayment($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function reconciliationReport(ReconciliationReportRequest $request)
    {
        $result = $this->service->reconciliationReport($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function monthlySummary(ReportMonthCycleRequest $request)
    {
        $result = $this->service->monthlySummary($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function recoveryReport(ReportMonthCycleRequest $request)
    {
        $result = $this->service->recoveryReport($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function employeeBillSummary(ReportMonthCycleRequest $request)
    {
        $result = $this->service->employeeBillSummary($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function vanReport(ReportMonthCycleRequest $request)
    {
        $result = $this->service->vanReport($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function elecSummary(ReportMonthCycleRequest $request)
    {
        $result = $this->service->elecSummary($request->validated() + ['unit_id' => request()->query('unit_id')]);
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function exportExcelReconciliation(ReportMonthCycleRequest $request)
    {
        $result = $this->service->exportExcelReconciliation($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        if ($code !== 200) {
            return response()->json($result, $code);
        }

        return response($result['content'], 200, [
            'Content-Type' => $result['content_type'],
            'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
        ]);
    }
}
