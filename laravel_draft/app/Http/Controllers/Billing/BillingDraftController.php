<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\BillingApproveRequest;
use App\Http\Requests\Billing\BillingFinalizeRequest;
use App\Http\Requests\Billing\BillingLockRequest;
use App\Http\Requests\Billing\BillingPrecheckRequest;
use App\Services\Billing\DraftBillingFlowService;

class BillingDraftController extends Controller
{
    public function __construct(private readonly DraftBillingFlowService $service)
    {
    }

    public function precheck(BillingPrecheckRequest $request)
    {
        return response()->json($this->service->precheck($request->validated()), 501);
    }

    public function finalize(BillingFinalizeRequest $request)
    {
        return response()->json($this->service->finalize($request->validated()), 501);
    }

    public function lock(BillingLockRequest $request)
    {
        return response()->json($this->service->lock($request->validated()), 501);
    }

    public function approve(BillingApproveRequest $request)
    {
        return response()->json($this->service->approve($request->validated()), 501);
    }
}
