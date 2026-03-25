<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\BillingAdjustmentApproveRequest;
use App\Http\Requests\Billing\BillingAdjustmentCreateRequest;
use App\Http\Requests\Billing\BillingApproveRequest;
use App\Http\Requests\Billing\BillingFinalizeRequest;
use App\Http\Requests\Billing\BillingLockRequest;
use App\Http\Requests\Billing\BillingPrecheckRequest;
use App\Http\Requests\Billing\ReconciliationReportRequest;
use App\Http\Requests\Billing\RecoveryPaymentRequest;
use App\Services\Billing\DraftBillingFlowService;

class BillingDraftController extends Controller
{
    public function __construct(private readonly DraftBillingFlowService $service)
    {
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
}
