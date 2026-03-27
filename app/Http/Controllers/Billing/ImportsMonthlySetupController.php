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
use Illuminate\Http\Request;

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

    public function errorReportLiteral()
    {
        $result = $this->service->errorReport('');
        $code = (int)($result['_http'] ?? 404);
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

    public function ratesUpsert(MonthlyRatesUpsertRequest $request)
    {
        return response()->json($this->service->monthlyRatesConfigUpsert($request->validated()));
    }

    public function ratesApprove(Request $request)
    {
        $payload = $request->validate([
            'month_cycle' => ['required', 'regex:/^\d{2}-\d{4}$/'],
        ]);

        $result = $this->service->ratesApprove($payload);
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function monthlyVariableGet(Request $request)
    {
        return response()->json($this->service->monthlyVariableGet([
            'month_cycle' => (string) $request->query('month_cycle', ''),
            'expense_type' => (string) $request->query('expense_type', ''),
        ]));
    }

    public function monthlyVariableUpsert(Request $request)
    {
        $payload = $request->validate([
            'month_cycle' => ['required', 'regex:/^\d{2}-\d{4}$/'],
            'expense_type' => ['nullable', 'string', 'max:64'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'rows' => ['nullable', 'array'],
            'rows.*.expense_code' => ['nullable', 'string', 'max:64'],
            'rows.*.expense_head' => ['nullable', 'string', 'max:255'],
            'rows.*.amount' => ['nullable', 'numeric'],
            'rows.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->service->monthlyVariableUpsert($payload + ['raw' => $request->all()]);
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
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
