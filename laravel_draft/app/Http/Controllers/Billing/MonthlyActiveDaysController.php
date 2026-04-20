<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\MonthlyActiveDaysImportRequest;
use App\Http\Requests\Billing\MonthlyActiveDaysPreviewRequest;
use App\Services\Billing\MonthlyActiveDaysImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MonthlyActiveDaysController extends Controller
{
    public function __construct(private readonly MonthlyActiveDaysImportService $service)
    {
    }

    public function index(Request $request)
    {
        $billingMonthDate = $this->normalizeMonth((string) ($request->query('billing_month_date') ?? now()->format('Y-m-01')));

        return view('ui.monthly-active-days', [
            'billingMonthDate' => $billingMonthDate,
            'rows' => $this->service->rowsForMonth($billingMonthDate),
        ]);
    }

    public function template()
    {
        $csv = "company_id,active_days,remarks\nE1001,30,Full month\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="monthly_active_days_template.csv"',
        ]);
    }

    public function rows(Request $request)
    {
        $billingMonthDate = $this->normalizeMonth((string) $request->query('billing_month_date', ''));
        if ($billingMonthDate === '') {
            return response()->json(['status' => 'error', 'error' => 'billing_month_date required'], 400);
        }

        return response()->json([
            'status' => 'ok',
            'billing_month_date' => $billingMonthDate,
            'rows' => $this->service->rowsForMonth($billingMonthDate),
        ]);
    }

    public function preview(MonthlyActiveDaysPreviewRequest $request)
    {
        $result = $this->service->preview(
            $request->validated()['billing_month_date'],
            $request->file('upload_file'),
            (bool) ($request->validated()['replace_existing'] ?? false),
        );

        if (($result['_http'] ?? null) !== null) {
            return response()->json($result, $result['_http']);
        }

        $token = (string) Str::uuid();
        $request->session()->put('monthly_active_days_preview.'.$token, $result);

        return response()->json($result + ['preview_token' => $token]);
    }

    public function import(MonthlyActiveDaysImportRequest $request)
    {
        $validated = $request->validated();
        $key = 'monthly_active_days_preview.'.$validated['preview_token'];
        $preview = $request->session()->get($key);

        if (!$preview) {
            return response()->json(['status' => 'error', 'error' => 'Preview expired or not found'], 404);
        }

        if (($preview['billing_month_date'] ?? null) !== $validated['billing_month_date']) {
            return response()->json(['status' => 'error', 'error' => 'Preview month mismatch'], 422);
        }

        $preview['summary']['replace_existing'] = (bool) ($validated['replace_existing'] ?? false);

        $result = $this->service->commit(
            $preview,
            (string) ($request->session()->get('user_id') ?? '')
        );

        $request->session()->forget($key);

        return response()->json($result + [
            'rows' => $this->service->rowsForMonth($validated['billing_month_date']),
        ]);
    }

    private function normalizeMonth(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value.'-01';
        }

        return $value;
    }
}
