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
        $validated = $request->validated();

        $result = $this->service->preview(
            $validated['billing_month_date'],
            $validated['cycle_start_date'],
            $validated['cycle_end_date'],
            $request->file('upload_file'),
            (bool) ($validated['replace_existing'] ?? false),
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

        if (($preview['cycle_start_date'] ?? null) !== $validated['cycle_start_date'] || ($preview['cycle_end_date'] ?? null) !== $validated['cycle_end_date']) {
            return response()->json(['status' => 'error', 'error' => 'Preview billing cycle mismatch'], 422);
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

    public function employees(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'employees' => $this->service->searchEmployees((string) $request->query('q', ''), 20),
        ]);
    }

    public function grid(Request $request)
    {
        $billingMonthDate = $this->normalizeMonth((string) $request->query('billing_month_date', ''));
        if ($billingMonthDate === '') {
            return response()->json(['status' => 'error', 'error' => 'billing_month_date required'], 400);
        }

        $cycle = $this->service->cycleForMonth($billingMonthDate);

        return response()->json([
            'status' => 'ok',
            'billing_month_date' => $billingMonthDate,
            'cycle' => $cycle,
            'rows' => $this->service->rowsForMonthDetailed($billingMonthDate, [
                'cycle_start_date' => $cycle['cycle_start_date'] ?? '',
                'q' => (string) $request->query('q', ''),
                'department' => (string) $request->query('department', ''),
                'status' => (string) $request->query('status', 'ALL'),
                'entry' => (string) $request->query('entry', 'ALL'),
                'source' => (string) $request->query('source', ''),
                'limit' => (int) $request->query('limit', 500),
            ]),
        ]);
    }

    public function storeRow(Request $request)
    {
        $billingMonthDate = $this->normalizeMonth((string) $request->input('billing_month_date', ''));
        $companyId = trim((string) $request->input('company_id', ''));
        if ($billingMonthDate === '' || $companyId === '') {
            return response()->json(['status' => 'error', 'error' => 'billing_month_date and company_id required'], 400);
        }

        $result = $this->service->upsertRow(
            $billingMonthDate,
            $companyId,
            $request->input('active_days'),
            $request->input('remarks'),
            (string) (optional($request->user())->name ?? $request->session()->get('auth_user_name', 'manual')),
            $request->input('cycle_start_date') ? (string) $request->input('cycle_start_date') : null,
            $request->input('cycle_end_date') ? (string) $request->input('cycle_end_date') : null
        );

        return response()->json($result, $result['_http'] ?? 200);
    }

    public function destroyRow(Request $request)
    {
        $billingMonthDate = $this->normalizeMonth((string) $request->input('billing_month_date', ''));
        $companyId = trim((string) $request->input('company_id', ''));
        if ($billingMonthDate === '' || $companyId === '') {
            return response()->json(['status' => 'error', 'error' => 'billing_month_date and company_id required'], 400);
        }

        $result = $this->service->deleteRow($billingMonthDate, $companyId);

        return response()->json($result, $result['_http'] ?? 200);
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
