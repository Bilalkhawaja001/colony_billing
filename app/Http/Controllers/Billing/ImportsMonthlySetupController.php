<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ImportsIngestPreviewRequest;
use App\Http\Requests\Billing\ImportsMarkValidatedRequest;
use App\Http\Requests\Billing\MonthOpenRequest;
use App\Http\Requests\Billing\MonthTransitionRequest;
use App\Http\Requests\Billing\MonthlyRatesConfigRequest;
use App\Http\Requests\Billing\MonthlyRatesInitializeRequest;
use App\Http\Requests\Billing\MonthlyRatesUpsertRequest;
use App\Services\Billing\ImportsMonthlySetupService;

class ImportsMonthlySetupController extends Controller
{
    public function __construct(private readonly ImportsMonthlySetupService $service)
    {
    }

    public function ingestPreview(ImportsIngestPreviewRequest $request)
    {
        return response()->json($this->service->ingestPreview($request->validated()));
    }

    public function markValidated(ImportsMarkValidatedRequest $request)
    {
        return response()->json($this->service->markValidated($request->validated()));
    }

    public function unitIdAliases()
    {
        return response()->json($this->service->unitIdAliases());
    }

    public function errorReport(string $token)
    {
        $result = $this->service->errorReport($token);
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function monthlyRatesInitialize(MonthlyRatesInitializeRequest $request)
    {
        return response()->json($this->service->monthlyRatesInitialize($request->validated()));
    }

    public function monthlyRatesConfig(MonthlyRatesConfigRequest $request)
    {
        $result = $this->service->monthlyRatesConfig($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function monthlyRatesHistory()
    {
        return response()->json($this->service->monthlyRatesHistory((int)request()->query('limit', 12)));
    }

    public function monthlyRatesConfigUpsert(MonthlyRatesUpsertRequest $request)
    {
        return response()->json($this->service->monthlyRatesConfigUpsert($request->validated()));
    }

    public function monthOpen(MonthOpenRequest $request)
    {
        return response()->json($this->service->monthOpen($request->validated()));
    }

    public function monthTransition(MonthTransitionRequest $request)
    {
        $result = $this->service->monthTransition($request->validated());
        $code = (int)($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }
}
