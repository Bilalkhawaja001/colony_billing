<?php

namespace App\Http\Controllers\Ui;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardParityService;
use Illuminate\Http\Request;

class ParityUiController extends Controller
{
    public function __construct(private readonly DashboardParityService $dashboard)
    {
    }

    private function renderUiPage(string $title, string $path)
    {
        return view('ui.page', [
            'title' => $title,
            'path' => $path,
        ]);
    }

    public function home()
    {
        return session()->has('user_id')
            ? redirect('/dashboard')
            : redirect('/login');
    }

    public function dashboard(Request $request)
    {
        $month = $this->dashboard->resolveMonthCycle($request->query('month_cycle'));

        return view('ui.dashboard', [
            'monthCycle' => $month,
            'kpis' => $this->dashboard->colonyKpis($month)['kpis'] ?? [],
            'familyRows' => $this->dashboard->familyMembers($month)['rows'] ?? [],
            'vanRows' => $this->dashboard->vanKids($month)['rows'] ?? [],
        ]);
    }

    public function dashboardV2(Request $request)
    {
        $month = $this->dashboard->resolveMonthCycle($request->query('month_cycle'));
        return view('ui.dashboard-v2', [
            'monthCycle' => $month,
            'kpis' => $this->dashboard->colonyKpis($month)['kpis'] ?? [],
            'familyRows' => $this->dashboard->familyMembers($month)['rows'] ?? [],
            'vanRows' => $this->dashboard->vanKids($month)['rows'] ?? [],
        ]);
    }

    public function reports(Request $request)
    {
        $data = $this->dashboard->reportsSummary($request->query('month_cycle'));

        return view('ui.reports', [
            'monthCycle' => $data['month_cycle'] ?? null,
            'rows' => $data['rows'] ?? [],
        ]);
    }

    public function reconciliation(Request $request)
    {
        $data = $this->dashboard->reconciliation($request->query('month_cycle'));

        return view('ui.reconciliation', [
            'monthCycle' => $data['month_cycle'] ?? null,
            'rows' => $data['rows'] ?? [],
        ]);
    }

    public function monthControl()
    {
        $data = $this->dashboard->monthControl();

        return view('ui.month-control', [
            'rows' => $data['rows'] ?? [],
        ]);
    }

    public function monthCycle(Request $request)
    {
        return view('ui.month-cycle', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'rows' => $this->dashboard->monthControl()['rows'] ?? [],
        ]);
    }

    public function imports(Request $request)
    {
        return view('ui.imports', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function billing(Request $request)
    {
        return view('ui.billing', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function monthlyActiveDays(Request $request)
    {
        return view('ui.monthly-active-days', [
            'billingMonthDate' => (string) ($request->query('billing_month_date') ?? now()->format('Y-m-01')),
            'rows' => [],
        ]);
    }

    public function elecSummary(Request $request)
    {
        return view('ui.elec-summary', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'unitId' => (string)($request->query('unit_id') ?? ''),
        ]);
    }

    public function familyDetails(Request $request)
    {
        return view('family-details', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'companyId' => (string)($request->query('company_id') ?? ''),
        ]);
    }

    public function resultsEmployeeWise(Request $request)
    {
        return view('results-employee-wise', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function resultsUnitWise(Request $request)
    {
        return view('results-unit-wise', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function logs(Request $request)
    {
        return view('logs', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function rates(Request $request)
    {
        return view('ui.rates', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }
    public function waterMeters(Request $request)
    {
        return view('ui.water-meters', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function van(Request $request)
    {
        return view('ui.van', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }
    public function employeeMaster() { return view('ui.employee-master'); }
    public function employees() { return view('ui.employees'); }
    public function employeeHelper() { return view('ui.employee-helper'); }
    public function unitMaster() { return view('ui.unit-master'); }


    public function familyList()
    {
        $cardFromUnit = static function ($unitId) {
            $unit = strtoupper(trim((string) $unitId));

            if (strpos($unit, 'WA-') === 0) {
                return 'House A Type Weaving';
            }

            if (strpos($unit, 'SA-') === 0) {
                return 'House A Type Spinning';
            }

            if (strpos($unit, 'WB-') === 0) {
                return 'House B Type Weaving';
            }

            if (strpos($unit, 'SB-') === 0) {
                return 'House B Type Spinning';
            }

            if (strpos($unit, 'WC-') === 0) {
                return 'House C Type Weaving';
            }

            if (strpos($unit, 'SC-') === 0) {
                return 'House C Type Spinning';
            }

            return null;
        };

        $rows = \Illuminate\Support\Facades\DB::table('family_members as f')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'f.company_id')
            ->orderBy('e.name')
            ->orderBy('f.member_name')
            ->get([
                'f.company_id',
                'e.name as employee',
                'f.member_name',
                'f.relation',
                'f.age',
                'f.school_going',
                'f.school_name',
                'f.class_name',
                'f.current_status',
                'f.is_active',
                'f.source_room_no',
                'e.unit_id as employee_unit_id',
            ])
            ->map(function ($r) use ($cardFromUnit) {
                $row = (array) $r;
                $sourceRoom = $row['source_room_no'] ?? '';
                $employeeUnit = $row['employee_unit_id'] ?? '';

                $effectiveUnit = $cardFromUnit($sourceRoom) !== null ? $sourceRoom : $employeeUnit;

                $row['effective_unit'] = $effectiveUnit;
                $row['card_label'] = $cardFromUnit($effectiveUnit);

                return $row;
            })
            ->all();

        $cardLabels = [
            'House A Type Weaving',
            'House A Type Spinning',
            'House B Type Weaving',
            'House B Type Spinning',
            'House C Type Weaving',
            'House C Type Spinning',
        ];

        $familyCards = collect($cardLabels)
            ->map(function ($label) use ($rows) {
                $matched = collect($rows)->filter(fn ($row) => ($row['card_label'] ?? null) === $label);

                return [
                    'label' => $label,
                    'family_rows' => $matched->count(),
                    'active_rows' => $matched->filter(function ($row) {
                        $active = strtoupper(trim((string)($row['is_active'] ?? '')));
                        return in_array($active, ['1', 'YES', 'ACTIVE', 'TRUE'], true);
                    })->count(),
                    'present_rows' => $matched->filter(fn ($row) => strtoupper(trim((string)($row['current_status'] ?? ''))) === 'PRESENT')->count(),
                    'distinct_employees' => $matched->pluck('company_id')->filter()->unique()->count(),
                ];
            })
            ->all();

        return view('ui.family-list', [
            'familyRows' => $rows,
            'familyCards' => $familyCards,
        ]);
    }

    // Phase-1: Monthly HR Active List Reconciliation (PREVIEW ONLY - no writes)
    public function staffCheck()
    {
        return view('ui.staff-check', ['result' => null]);
    }

    public function staffCheckCompare(\Illuminate\Http\Request $request)
    {
        // PREVIEW ONLY: no persistence, no master write, no file save.
        $file = $request->file('csv');
        if (!$file) {
            return view('ui.staff-check', ['result' => ['error' => 'No CSV file uploaded.']]);
        }

        // Parse uploaded CSV in-memory. Company ID kept as TEXT (leading zeros preserved).
        $path = $file->getRealPath();
        $handle = @fopen($path, 'r');
        if ($handle === false) {
            return view('ui.staff-check', ['result' => ['error' => 'Could not read uploaded file.']]);
        }

        $header = fgetcsv($handle);
        if ($header === false || $header === null) {
            fclose($handle);
            return view('ui.staff-check', ['result' => ['error' => 'CSV is empty or unreadable.']]);
        }
        // normalise header names
        $norm = array_map(fn($h) => strtolower(trim((string)$h)), $header);
        $idx = [
            'company_id' => array_search('company id', $norm),
            'name'       => array_search('employee name', $norm),
            'department' => array_search('department', $norm),
            'designation'=> array_search('designation', $norm),
        ];
        if ($idx['company_id'] === false || $idx['name'] === false) {
            fclose($handle);
            return view('ui.staff-check', ['result' => [
                'error' => 'Required columns missing. CSV must have headers: Company ID, Employee Name.',
                'found_headers' => $header,
            ]]);
        }

        $hrRows = [];
        $hrSeen = [];           // company_id => count (CSV-internal duplicate detection)
        $invalid = [];          // invalid/duplicate/conflict bucket
        $lineNo = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;
            $cid = isset($row[$idx['company_id']]) ? trim((string)$row[$idx['company_id']]) : '';
            $nm  = ($idx['name'] !== false && isset($row[$idx['name']])) ? trim((string)$row[$idx['name']]) : '';
            $dept= ($idx['department'] !== false && isset($row[$idx['department']])) ? trim((string)$row[$idx['department']]) : '';
            $desg= ($idx['designation'] !== false && isset($row[$idx['designation']])) ? trim((string)$row[$idx['designation']]) : '';

            if ($cid === '') {
                $invalid[] = ['line' => $lineNo, 'company_id' => '', 'name' => $nm, 'issue' => 'Missing Company ID'];
                continue;
            }
            if (isset($hrSeen[$cid])) {
                $hrSeen[$cid]++;
                $invalid[] = ['line' => $lineNo, 'company_id' => $cid, 'name' => $nm, 'issue' => 'Duplicate Company ID in CSV'];
                continue;
            }
            $hrSeen[$cid] = 1;
            $hrRows[$cid] = ['company_id' => $cid, 'name' => $nm, 'department' => $dept, 'designation' => $desg];
        }
        fclose($handle);

        // READ DB active employees (definition: active IN ('Yes','1') — per ReadinessService)
        $dbActive = \Illuminate\Support\Facades\DB::table('employees_master')
            ->whereIn('active', ['Yes', '1'])
            ->get(['company_id', 'name', 'unit_id'])
            ->keyBy(fn($r) => trim((string)$r->company_id));

        $dbActiveIds = $dbActive->keys()->all();
        $hrIds = array_keys($hrRows);

        // Compare
        $matched = [];
        $dbMissingInHr = [];
        $hrMissingInDb = [];

        foreach ($hrRows as $cid => $hr) {
            if ($dbActive->has($cid)) {
                $db = $dbActive->get($cid);
                $nameMismatch = $hr['name'] !== '' && strcasecmp(trim($hr['name']), trim((string)$db->name)) !== 0;
                $matched[] = [
                    'company_id' => $cid,
                    'hr_name' => $hr['name'],
                    'db_name' => (string)$db->name,
                    'unit_id' => (string)($db->unit_id ?? ''),
                    'name_mismatch' => $nameMismatch,
                ];
                if ($nameMismatch) {
                    $invalid[] = ['line' => null, 'company_id' => $cid, 'name' => $hr['name'], 'issue' => 'Name mismatch (DB: '.$db->name.')'];
                }
            } else {
                $hrMissingInDb[] = $hr;
            }
        }
        foreach ($dbActive as $cid => $db) {
            if (!isset($hrRows[$cid])) {
                $dbMissingInHr[] = ['company_id' => $cid, 'name' => (string)$db->name, 'unit_id' => (string)($db->unit_id ?? '')];
            }
        }

        $result = [
            'error' => null,
            'matched' => $matched,
            'db_missing_in_hr' => $dbMissingInHr,
            'hr_missing_in_db' => $hrMissingInDb,
            'invalid' => $invalid,
            'counts' => [
                'hr_csv_total' => count($hrRows) + count(array_filter($invalid, fn($i) => ($i['issue'] ?? '') === 'Duplicate Company ID in CSV' || ($i['issue'] ?? '') === 'Missing Company ID')),
                'hr_valid_rows' => count($hrRows),
                'db_active_total' => count($dbActiveIds),
                'matched' => count($matched),
                'db_missing_in_hr' => count($dbMissingInHr),
                'hr_missing_in_db' => count($hrMissingInDb),
                'invalid' => count($invalid),
            ],
        ];

        return view('ui.staff-check', ['result' => $result]);
    }

    public function staffCheckAction(\Illuminate\Http\Request $request)
    {
        // Direct DB action (single row). RBAC gated. Audit logged. Transaction.
        $role = session('role');
        if (!in_array($role, ['SUPER_ADMIN', 'BILLING_ADMIN'], true)) {
            return response()->json(['status' => 'error', 'error' => 'Forbidden. Requires Billing Admin or Super Admin.'], 403);
        }

        $cid    = trim((string) $request->input('cid'));
        $action = trim((string) $request->input('action'));
        if ($cid === '' || $action === '') {
            return response()->json(['status' => 'error', 'error' => 'Missing company id or action.'], 422);
        }

        $emp = \Illuminate\Support\Facades\DB::table('employees_master')->where('company_id', $cid)->first();
        if (!$emp) {
            return response()->json(['status' => 'error', 'error' => 'Employee not found: ' . $cid], 404);
        }

        $actor = session('username') ?? ('user#' . (session('user_id') ?? '?'));
        $corr  = 'staffcheck-' . date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 6);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $before = (array) $emp;
            $update = [];
            $auditAction = '';

            if ($action === 'mark_left') {
                $lwd    = trim((string) $request->input('lwd'));
                $reason = trim((string) $request->input('reason'));
                if ($lwd === '' || $reason === '') {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json(['status' => 'error', 'error' => 'Last Working Date and Reason are required.'], 422);
                }
                if (!in_array((string) $emp->active, ['Yes', '1'], true)) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json(['status' => 'error', 'error' => 'Employee is already not active.'], 409);
                }
                $update = ['active' => '0', 'leave_date' => $lwd, 'updated_at' => now()];
                $auditAction = 'STAFF_CHECK_MARK_LEFT';

            } elseif ($action === 'mark_outside') {
                $update = ['unit_id' => 'OUTSIDE', 'colony_type' => 'OUTSIDE', 'updated_at' => now()];
                $auditAction = 'STAFF_CHECK_MARK_OUTSIDE';

            } elseif ($action === 'edit') {
                $name = trim((string) $request->input('name'));
                $dept = trim((string) $request->input('department'));
                $desg = trim((string) $request->input('designation'));
                if ($name === '') {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json(['status' => 'error', 'error' => 'Name cannot be empty.'], 422);
                }
                $update = ['name' => $name, 'department' => $dept, 'designation' => $desg, 'updated_at' => now()];
                $auditAction = 'STAFF_CHECK_EDIT';

            } else {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json(['status' => 'error', 'error' => 'Unknown action: ' . $action], 422);
            }

            \Illuminate\Support\Facades\DB::table('employees_master')->where('company_id', $cid)->update($update);
            $after = array_merge($before, $update);

            \Illuminate\Support\Facades\DB::table('audit_log')->insert([
                'action' => $auditAction,
                'entity_type' => 'employees_master',
                'entity_id' => $cid,
                'actor_username' => $actor,
                'actor_user_id' => session('user_id'),
                'before_json' => json_encode(['active' => $before['active'] ?? null, 'leave_date' => $before['leave_date'] ?? null, 'name' => $before['name'] ?? null, 'unit_id' => $before['unit_id'] ?? null, 'colony_type' => $before['colony_type'] ?? null]),
                'after_json' => json_encode(['active' => $after['active'] ?? null, 'leave_date' => $after['leave_date'] ?? null, 'name' => $after['name'] ?? null, 'unit_id' => $after['unit_id'] ?? null, 'colony_type' => $after['colony_type'] ?? null]),
                'meta_json' => json_encode(['reason' => $request->input('reason'), 'remarks' => $request->input('remarks')]),
                'correlation_id' => $corr,
                'ip_address' => $request->ip(),
                'session_id' => $request->session()->getId(),
                'created_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'error' => 'Action failed, rolled back: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'ok', 'cid' => $cid, 'action' => $action, 'correlation_id' => $corr]);
    }

    // Hub: Meters & Readings
    public function metersHub()
    {
        return view('ui.meters-hub');
    }

    // Workspace: Meter Registry (unit↔meter mapping + registry tooling)
    public function meterRegistry()
    {
        return view('ui.meter-registry');
    }

    // Workspace: Readings (operator readings console)
    public function meterReadings()
    {
        return view('ui.meter-readings');
    }

    // Workspace: Water Tools (pre-billing water controls/allocation tools)
    public function waterTools(Request $request)
    {
        return view('ui.water-tools', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function meterMaster() { return view('ui.meter-master'); }
    public function meterRegisterIngest() { return view('ui.meter-register-ingest'); }
    public function rooms() { return view('ui.rooms'); }
    public function occupancy() { return view('ui.occupancy'); }
    public function electricV1Run() { return $this->renderUiPage('Electric V1 Run', '/ui/electric-v1-run'); }
    public function electricV1Outputs() { return $this->renderUiPage('Electric V1 Outputs', '/ui/electric-v1-outputs'); }
    public function mastersEmployees() { return view('ui.employee-master'); }
    public function mastersUnits() { return view('ui.unit-master'); }
    public function mastersMeters() { return view('ui.meter-master'); }
    public function mastersRates() { return view('ui.rates'); }
    public function inputsMapping() { return view('ui.inputs-mapping'); }
    public function inputsHr() { return view('ui.inputs-hr'); }
    public function inputsReadings() { return view('ui.inputs-readings'); }
    public function inputsRo() { return view('ui.inputs-ro'); }
    public function finalizedMonths(Request $request)
    {
        return view('ui.finalized-months', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'rows' => $this->dashboard->monthControl()['rows'] ?? [],
        ]);
    }

    public function colonyKpis(Request $request)
    {
        return response()->json($this->dashboard->colonyKpis($request->query('month_cycle')));
    }

    public function familyMembers(Request $request)
    {
        return response()->json($this->dashboard->familyMembers($request->query('month_cycle')));
    }

    public function vanKids(Request $request)
    {
        return response()->json($this->dashboard->vanKids($request->query('month_cycle')));
    }
}
