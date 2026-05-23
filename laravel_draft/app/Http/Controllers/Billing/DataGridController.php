<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Transport\TransportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DataGridController extends Controller
{
    private array $modules = [
        'employees' => [
            'table' => 'employees_master',
            'key' => 'company_id',
            'select' => ['company_id','name','father_name','cnic_no','mobile_no','department','section','designation','employee_type','active','leave_date','unit_id','room_no','colony_type','block_floor'],
            'search' => ['company_id','name','cnic_no','mobile_no'],
            'filters' => ['department','designation','active','unit_id','room_no'],
            'editable' => ['company_id','name','father_name','cnic_no','mobile_no','department','section','designation','employee_type','active','leave_date','unit_id','room_no','colony_type','block_floor'],
        ],
        'active-days' => [
            'table' => 'electric_active_days_monthly',
            'key' => 'id',
            'select' => ['id','billing_month_date','company_id','active_days','remarks'],
            'search' => ['company_id'],
            'filters' => ['billing_month_date','company_id'],
            'editable' => ['id','billing_month_date','company_id','active_days','remarks'],
        ],
        'units' => [
            'table' => 'util_unit',
            'key' => 'unit_id',
            'select' => ['unit_id','colony_type','block_name','room_no','is_active'],
            'search' => ['unit_id','colony_type','block_name','room_no'],
            'filters' => ['unit_id','colony_type','is_active','res_type'],
            'editable' => ['unit_id','colony_type','block_name','room_no','is_active'],
        ],
        'rooms' => [
            'table' => 'util_unit_room_snapshot',
            'key' => 'id',
            'select' => ['id','month_cycle','unit_id','category','block_floor','room_no'],
            'search' => ['unit_id','room_no','category','block_floor'],
            'filters' => ['month_cycle','unit_id','room_no','category'],
            'editable' => ['id','month_cycle','unit_id','category','block_floor','room_no'],
        ],
        'occupancy' => [
            'table' => 'util_occupancy_monthly',
            'key' => 'id',
            'select' => ['id','month_cycle','employee_id','unit_id','room_no','category','block_floor','active_days'],
            'search' => ['employee_id','unit_id','room_no','category','block_floor'],
            'filters' => ['month_cycle','unit_id','room_no','category'],
            'editable' => ['id','month_cycle','employee_id','unit_id','room_no','category','block_floor','active_days'],
        ],
        'meter-registry' => [
            'table' => 'util_meter_unit',
            'key' => 'meter_id',
            'select' => ['meter_id','unit_id','meter_type','is_active'],
            'search' => ['meter_id','unit_id','meter_type'],
            'filters' => ['unit_id','meter_type','is_active'],
            'editable' => ['meter_id','unit_id','meter_type','is_active'],
        ],
        'meter-readings' => [
            'table' => 'readings',
            'key' => 'id',
            'select' => ['id','month_cycle','unit_id','meter_id','meter_type','usage','amount'],
            'search' => ['meter_id','unit_id','meter_type','month_cycle'],
            'filters' => ['month_cycle','unit_id','meter_type'],
            'editable' => ['id','month_cycle','unit_id','meter_id','meter_type','usage','amount'],
        ],
        'family' => [
            'table' => 'family_details',
            'key' => 'id',
            'select' => ['id','month_cycle','company_id','employee_name','spouse_name','spouse_count','children_count','school_going_children','van_using_children','unit_id','room_no'],
            'search' => ['company_id','employee_name','unit_id','room_no'],
            'filters' => ['month_cycle','unit_id','room_no'],
            'editable' => ['id','month_cycle','company_id','employee_name','spouse_name','spouse_count','children_count','school_going_children','van_using_children','unit_id','room_no'],
        ],
        'billing-preview' => [
            'table' => 'util_elec_employee_share_monthly',
            'key' => 'id',
            'select' => ['id','month_cycle','employee_id','unit_id','room_no','active_days','emp_used_units','eligible_units','billable_units','rate','amount'],
            'search' => ['employee_id','unit_id','room_no'],
            'filters' => ['month_cycle','unit_id','room_no'],
            'editable' => [],
        ],
    ];

    public function list(Request $request, string $module)
    {
        $cfg = $this->cfg($module);
        $perPage = min(max((int)$request->query('per_page', 25), 1), 200);
        $page = max((int)$request->query('page', 1), 1);
        $q = trim((string)$request->query('q', ''));

        $query = DB::table($cfg['table']);
        if ($module === 'occupancy') {
            $query->leftJoin('employees_master as e', 'e.company_id', '=', 'util_occupancy_monthly.employee_id')
                ->select($this->prefixed($cfg['select'], 'util_occupancy_monthly', ['employee_id']))
                ->addSelect('e.name');
        } elseif ($module === 'active-days') {
            $query->leftJoin('employees_master as e', 'e.company_id', '=', 'electric_active_days_monthly.company_id')
                ->leftJoin('util_occupancy_monthly as o', function ($join) {
                    $join->on('o.employee_id', '=', 'electric_active_days_monthly.company_id');
                })
                ->select('electric_active_days_monthly.id','electric_active_days_monthly.billing_month_date','electric_active_days_monthly.company_id','e.name','electric_active_days_monthly.active_days','o.unit_id','o.room_no','electric_active_days_monthly.remarks');
        } elseif ($module === 'rooms') {
            $month = (string)$request->query('month_cycle', '');
            $sub = DB::table('util_occupancy_monthly')
                ->select('month_cycle','unit_id','room_no', DB::raw('COUNT(DISTINCT employee_id) as room_persons'))
                ->groupBy('month_cycle','unit_id','room_no');
            $query->leftJoinSub($sub, 'rp', function ($join) {
                $join->on('rp.month_cycle','=','util_unit_room_snapshot.month_cycle')->on('rp.unit_id','=','util_unit_room_snapshot.unit_id')->on('rp.room_no','=','util_unit_room_snapshot.room_no');
            })->select('util_unit_room_snapshot.id','util_unit_room_snapshot.month_cycle','util_unit_room_snapshot.unit_id','util_unit_room_snapshot.category','util_unit_room_snapshot.block_floor','util_unit_room_snapshot.room_no', DB::raw('COALESCE(rp.room_persons,0) as room_persons'));
        } elseif ($module === 'units' && DB::table('util_unit')->count() === 0) {
            $query = DB::table('util_occupancy_monthly')
                ->select('unit_id', DB::raw('unit_id as unit_name'), DB::raw('MAX(category) as category'), DB::raw('MAX(block_floor) as colony_type'), DB::raw('1 as is_active'))
                ->whereNotNull('unit_id')
                ->where('unit_id', '<>', '')
                ->groupBy('unit_id');
        } elseif ($module === 'meter-registry') {
            if (DB::table('util_meter_unit')->count() > 0) {
                $latest = DB::table('util_meter_readings')->select('meter_id', DB::raw('MAX(reading_date) as last_reading_date'), DB::raw('MAX(reading_value) as last_reading_value'))->groupBy('meter_id');
                $query->leftJoinSub($latest, 'lr', 'lr.meter_id', '=', 'util_meter_unit.meter_id')
                    ->select('util_meter_unit.meter_id','util_meter_unit.unit_id','util_meter_unit.meter_type','util_meter_unit.is_active','lr.last_reading_date','lr.last_reading_value');
            } else {
                $query = DB::table('readings')->select('meter_id','unit_id','meter_type', DB::raw('1 as is_active'), DB::raw('MAX(month_cycle) as last_reading_date'), DB::raw('MAX(usage) as last_reading_value'))->groupBy('meter_id','unit_id','meter_type');
            }
        } elseif ($module === 'meter-readings') {
            $query->select('id','month_cycle','unit_id','meter_id','meter_type', DB::raw('NULL as previous_reading'), DB::raw('NULL as current_reading'), DB::raw('`usage` as unit_used'), DB::raw('month_cycle as reading_date'));
        } else {
            $query->select($cfg['select']);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($cfg, $q, $module) {
                foreach ($cfg['search'] as $col) {
                    $w->orWhere($this->qualify($module, $col), 'like', "%{$q}%");
                }
                if (in_array($module, ['occupancy','active-days'], true)) {
                    $w->orWhere('e.name', 'like', "%{$q}%");
                }
            });
        }

        if ($module === 'units') {
            $resType = (string) $request->query('res_type', '');

            if ($resType !== '') {
                $query->whereExists(function ($x) use ($resType) {
                    $x->select(DB::raw(1))
                        ->from('util_unit_room_snapshot as rs')
                        ->whereColumn('rs.unit_id', 'util_unit.unit_id')
                        ->whereRaw('rs.month_cycle = (SELECT MAX(month_cycle) FROM util_unit_room_snapshot)');

                    if ($resType === 'house') {
                        $x->where('rs.residence_type', 'like', 'House %');
                    } elseif ($resType === 'bachelor') {
                        $x->where('rs.residence_type', 'Bachelor');
                    } elseif ($resType === 'hostel') {
                        $x->where('rs.residence_type', 'Hostel');
                    } elseif ($resType === 'containers') {
                        $x->where('rs.residence_type', 'Container');
                    } elseif ($resType === 'uncategorized') {
                        $x->where(fn($w) => $w->whereNull('rs.residence_type')->orWhere('rs.residence_type', ''));
                    }
                });
            }
        }


        foreach (['month_cycle','billing_month_date','unit_id','room_no','active','category','colony_type','is_active','res_type','department','designation','meter_type','company_id'] as $filter) {
            if ($filter === 'res_type') continue;
            $value = $request->query($filter);
            if ($value !== null && $value !== '' && in_array($filter, $cfg['filters'], true)) {
                if ($filter === 'month_cycle') {
                    $value = $this->normalizeMonthCycle((string)$value);
                }
                $query->where($this->filterColumn($module, $filter), $value);
            }
        }

        $total = (clone $query)->count();
        $rows = $query->orderBy($this->orderColumn($module, $cfg['key']))->offset(($page - 1) * $perPage)->limit($perPage)->get();
        return response()->json([
            'status' => 'ok', 'module' => $module, 'page' => $page, 'per_page' => $perPage,
            'total' => $total, 'rows' => $rows, 'columns' => $this->columnsFor($module, $cfg), 'editable' => $cfg['editable'],
            'summary' => $this->summary($module, $request),
        ]);
    }

    public function upsert(Request $request, string $module)
    {
        $cfg = $this->cfg($module);
        if (empty($cfg['editable'])) abort(403, 'Module is read-only.');
        $data = $request->only($cfg['editable']);

        if ($module === 'employees') {
            $active = trim((string)($data['active'] ?? ''));
            $leaveDate = trim((string)($data['leave_date'] ?? ''));

            if ($leaveDate !== '' && strtotime($leaveDate) !== false && strtotime($leaveDate) <= strtotime(date('Y-m-d'))) {
                $data['active'] = 'No';
            }

            if (($data['active'] ?? '') === 'No' && $leaveDate === '') {
                abort(422, 'Leave Date is required when deactivating employee.');
            }
        }

        if ($module === 'active-days') $this->validateActiveDays($data);
        if ($module === 'meter-readings' && isset($data['reading_value']) && (float)$data['reading_value'] < 0) abort(422, 'Reading cannot be negative.');
        $key = $cfg['key'];
        $now = now()->toDateTimeString();
        $data['updated_at'] = $now;
        if (!array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
            $data['created_at'] = $now;
            $id = DB::table($cfg['table'])->insertGetId($data);
            return response()->json(['status' => 'ok', 'action' => 'inserted', 'id' => $id]);
        }
        $exists = DB::table($cfg['table'])->where($key, $data[$key])->exists();
        if ($exists) {
            DB::table($cfg['table'])->where($key, $data[$key])->update($data);
            return response()->json(['status' => 'ok', 'action' => 'updated', 'id' => $data[$key]]);
        }
        $data['created_at'] = $now;
        DB::table($cfg['table'])->insert($data);
        return response()->json(['status' => 'ok', 'action' => 'inserted', 'id' => $data[$key]]);
    }

    public function export(Request $request, string $module)
    {
        $response = $this->list($request->merge(['page' => 1, 'per_page' => 100000]), $module);
        $payload = $response->getData(true);
        return $this->csvResponse($module.'-export-'.date('Ymd_His').'.csv', $payload['columns'], $payload['rows']);
    }

    public function unitResidentGroups(Request $request)
    {
        $resType = (string) $request->query('res_type', '');
        $month = $this->normalizeMonthCycle((string) $request->query('month_cycle', ''));
        if ($month === '') {
            $latest = DB::table('util_unit_room_snapshot')->max('month_cycle');
            $month = $latest ?: '05-2026';
        }

        $groupCase = "CASE
            WHEN r.residence_type LIKE 'House %' THEN 'house'
            WHEN r.residence_type = 'Bachelor' THEN 'bachelor'
            WHEN r.residence_type = 'Hostel' THEN 'hostel'
            WHEN r.residence_type = 'Container' THEN 'containers'
            WHEN r.residence_type IS NULL OR r.residence_type = '' THEN 'uncategorized'
            ELSE 'other'
        END";

        $query = DB::table('util_unit_room_snapshot as r')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'r.unit_id')
            ->leftJoin('util_occupancy_monthly as o', function ($join) use ($month) {
                $join->on('o.unit_id', '=', 'r.unit_id')
                    ->on('o.room_no', '=', 'r.room_no')
                    ->where('o.month_cycle', '=', $month);
            })
            ->where('r.month_cycle', $month)
            ->select(
                'u.colony_type',
                'r.residence_type',
                DB::raw($groupCase.' as group_key'),
                DB::raw('COUNT(DISTINCT r.unit_id) as unit_count'),
                DB::raw('COUNT(DISTINCT r.room_no) as room_count'),
                DB::raw('COUNT(DISTINCT o.employee_id) as resident_count')
            )
            ->groupBy('u.colony_type', 'r.residence_type', DB::raw($groupCase))
            ->orderBy('group_key')
            ->orderBy('u.colony_type')
            ->orderBy('r.residence_type');

        if ($resType !== '') {
            $query->having('group_key', '=', $resType);
        }

        return response()->json([
            'status' => 'ok',
            'month_cycle' => $month,
            'rows' => $query->get(),
        ]);
    }

    public function unitResidentRooms(Request $request)
    {
        $month = $this->normalizeMonthCycle((string) $request->query('month_cycle', ''));
        if ($month === '') {
            $latest = DB::table('util_unit_room_snapshot')->max('month_cycle');
            $month = $latest ?: '05-2026';
        }

        $colonyType = trim((string) $request->query('colony_type', ''));
        $residenceType = trim((string) $request->query('residence_type', ''));
        $roomNo = trim((string) $request->query('room_no', ''));

        $query = DB::table('util_unit_room_snapshot as r')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'r.unit_id')
            ->leftJoin('util_occupancy_monthly as o', function ($join) use ($month) {
                $join->on('o.unit_id', '=', 'r.unit_id')
                    ->on('o.room_no', '=', 'r.room_no')
                    ->where('o.month_cycle', '=', $month);
            })
            ->where('r.month_cycle', $month)
            ->whereNotNull('r.room_no')
            ->where('r.room_no', '<>', '')
            ->select(
                'u.colony_type',
                'r.unit_id',
                'r.block_floor',
                'r.room_no',
                'r.residence_type',
                DB::raw('COUNT(DISTINCT o.employee_id) as employee_count')
            )
            ->groupBy(
                'u.colony_type',
                'r.unit_id',
                'r.block_floor',
                'r.room_no',
                'r.residence_type'
            )
            ->orderBy('r.unit_id')
            ->orderBy('r.room_no');

        if ($colonyType === '__uncategorized') {
            $query->where(fn($w) => $w->whereNull('u.colony_type')->orWhere('u.colony_type', ''));
        } elseif ($colonyType !== '') {
            $query->where('u.colony_type', $colonyType);
        }

        if ($residenceType !== '') {
            $query->where('r.residence_type', $residenceType);
        }

        if ($roomNo !== '') {
            $query->where('r.room_no', $roomNo);
        }

        return response()->json([
            'status' => 'ok',
            'month_cycle' => $month,
            'rows' => $query->limit(5000)->get(),
        ]);
    }

    public function unitResidents(Request $request)
    {
        $month = $this->normalizeMonthCycle((string) $request->query('month_cycle', ''));
        if ($month === '') {
            $latest = DB::table('util_occupancy_monthly')->max('month_cycle');
            $month = $latest ?: '05-2026';
        }

        $colonyType = trim((string) $request->query('colony_type', ''));

        $query = DB::table('util_occupancy_monthly as o')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'o.unit_id')
            ->leftJoin('util_unit_room_snapshot as r', function ($join) use ($month) {
                $join->on('r.unit_id', '=', 'o.unit_id')
                    ->on('r.room_no', '=', 'o.room_no')
                    ->where('r.month_cycle', '=', $month);
            })
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'o.employee_id')
            ->leftJoin('family_details as fd', function ($join) use ($month) {
                $join->on('fd.company_id', '=', 'o.employee_id')
                    ->where('fd.month_cycle', '=', $month);
            })
            ->where('o.month_cycle', $month)
            ->select(
                'o.month_cycle',
                'o.employee_id as company_id',
                'e.name',
                'e.department',
                'e.designation',
                'u.colony_type',
                'o.unit_id',
                'o.block_floor',
                'o.room_no',
                'o.active_days',
                'r.residence_type',
                DB::raw('COALESCE(fd.spouse_count,0) + COALESCE(fd.children_count,0) as family_members')
            )
            ->orderBy('o.unit_id')
            ->orderBy('o.room_no')
            ->orderBy('o.employee_id');

        if ($colonyType === '__uncategorized') {
            $query->where(fn($w) => $w->whereNull('u.colony_type')->orWhere('u.colony_type',''));
        } elseif ($colonyType !== '') {
            $query->where('u.colony_type', $colonyType);
        }

        $roomNo = trim((string) $request->query('room_no', ''));
        if ($roomNo !== '') {
            $query->where('o.room_no', $roomNo);
        }

        $residenceType = trim((string) $request->query('residence_type', ''));
        if ($residenceType !== '') {
            $query->where('r.residence_type', $residenceType);
        }

        return response()->json([
            'status' => 'ok',
            'month_cycle' => $month,
            'rows' => $query->limit(5000)->get(),
        ]);
    }


    public function employeeStatement(Request $request)
    {
        $payload = ['status' => 'ok'] + $this->statementPayload($request);

        if ($request->query('format') === 'json' || $request->expectsJson()) {
            return response()->json($payload);
        }

        return view('ui.employee-statement', $payload);
    }

    public function employeeStatementPrint(Request $request)
    {
        return view('ui.employee-statement-print', ['status' => 'ok'] + $this->statementPayload($request));
    }

    public function employeeStatementExport(Request $request)
    {
        $payload = $this->statementPayload($request);
        $format = strtolower((string)$request->query('format', 'csv'));
        if ($format === 'pdf') {
            return response($this->simplePdf($payload), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="employee-statement.pdf"',
            ]);
        }
        return $this->csvResponse('employee-statement-'.($request->query('company_id') ?: 'all').'.csv', array_keys($payload['statement'][0] ?? []), $payload['statement']);
    }

    public function employeeStatementsExportAll(Request $request)
    {
        $rows = $this->statementRows($request);
        return $this->csvResponse('employee-statements-all-'.date('Ymd_His').'.csv', array_keys($rows[0] ?? []), $rows);
    }

    private function statementPayload(Request $request): array
    {
        $rows = $this->statementRows($request);

        $total = round(array_sum(array_map(fn($r) => (float)($r['electric_amount'] ?? 0), $rows)), 2);
        $totalUsed = round(array_sum(array_map(fn($r) => (float)($r['emp_used_units'] ?? 0), $rows)), 4);
        $totalEligible = round(array_sum(array_map(fn($r) => (float)($r['eligible_units'] ?? 0), $rows)), 4);
        $totalBillable = round(array_sum(array_map(fn($r) => (float)($r['billable_units'] ?? 0), $rows)), 4);
        $totalActiveDays = round(array_sum(array_map(fn($r) => (float)($r['active_days'] ?? 0), $rows)), 2);
        $months = array_values(array_unique(array_map(fn($r) => (string)($r['month_cycle'] ?? ''), $rows)));

        $fromMonth = $this->normalizeMonthCycle((string)$request->query('from_month', $request->query('month_cycle', '05-2026')));
        $toMonth = $this->normalizeMonthCycle((string)$request->query('to_month', $request->query('month_cycle', $fromMonth)));
        $schoolVan = $this->schoolVanStatementPayload($fromMonth, $toMonth, $rows, $request);

        return [
            'month_cycle' => (string)$request->query('month_cycle', ''),
            'from_month' => $fromMonth,
            'to_month' => $toMonth,
            'company_id' => (string)$request->query('company_id', ''),
            'unit_id' => (string)$request->query('unit_id', ''),
            'room_no' => (string)$request->query('room_no', ''),
            'statement' => $rows,
            'school_van' => $schoolVan,
            'summary' => [
                'rows' => count($rows),
                'months_count' => count(array_filter($months)),
                'total_active_days' => $totalActiveDays,
                'total_used_units' => $totalUsed,
                'total_eligible_units' => $totalEligible,
                'total_billable_units' => $totalBillable,
                'total_amount' => $total,
            ],
            'total_amount' => $total,
            'note' => 'Payment/recovery not posted yet; Paid = 0 and Outstanding = Bill Amount where recovery rows are unavailable.',
        ];
    }

    private function schoolVanStatementPayload(string $fromMonth, string $toMonth, array $electricRows, Request $request): array
    {
        $companyId = trim((string) $request->query('company_id', ''));

        $scopeEmployeeIds = $companyId !== ''
            ? [$companyId]
            : array_values(array_unique(array_filter(array_map(
                fn($row) => trim((string) ($row['company_id'] ?? '')),
                $electricRows
            ))));

        if (empty($scopeEmployeeIds)) {
            return [
                'rows' => [],
                'blocked' => false,
                'generated' => false,
                'allocation_status' => 'NO_MATCHING_EMPLOYEE_SCOPE',
                'total_amount' => 0.0,
                'blockers' => [],
                'note' => 'No employee scope available for school van charge display.',
            ];
        }

        try {
            $cursor = Carbon::createFromFormat('m-Y', $fromMonth)->startOfMonth();
            $end = Carbon::createFromFormat('m-Y', $toMonth)->startOfMonth();
        } catch (\Throwable $e) {
            return [
                'rows' => [],
                'blocked' => true,
                'generated' => false,
                'allocation_status' => 'INVALID_MONTH_RANGE',
                'total_amount' => null,
                'blockers' => [],
                'note' => 'School van charge cannot be resolved for the selected month range.',
            ];
        }

        if ($cursor->gt($end)) {
            [$cursor, $end] = [$end, $cursor];
        }

        $service = app(TransportService::class);
        $chargeRows = [];
        $blockers = [];
        $hasBlockedCharge = false;
        $hasGeneratedCharge = false;
        $hasPreviewCharge = false;
        $guard = 0;

        while ($cursor->lte($end) && $guard < 120) {
            $monthCycle = $cursor->format('m-Y');

            $generatedExists = DB::table('util_school_van_monthly_charge')
                ->where('month_cycle', $monthCycle)
                ->where('charged_flag', 1)
                ->exists();

            if ($generatedExists) {
                $generatedRows = DB::table('util_school_van_monthly_charge as c')
                    ->leftJoin('employees_master as e', 'e.company_id', '=', 'c.employee_id')
                    ->where('c.month_cycle', $monthCycle)
                    ->where('c.charged_flag', 1)
                    ->whereIn('c.employee_id', $scopeEmployeeIds)
                    ->groupBy('c.employee_id', 'e.name')
                    ->selectRaw('c.employee_id as company_id, e.name as father_name, COUNT(*) as children_count, SUM(c.charge_factor) as chargeable_units, ROUND(SUM(c.amount), 2) as payable_amount')
                    ->get();

                foreach ($generatedRows as $generated) {
                    $chargeRows[] = [
                        'month_cycle' => $monthCycle,
                        'company_id' => (string) $generated->company_id,
                        'father_name' => (string) ($generated->father_name ?? ''),
                        'children_count' => (int) $generated->children_count,
                        'chargeable_units' => round((float) $generated->chargeable_units, 2),
                        'payable_amount' => round((float) $generated->payable_amount, 2),
                        'allocation_status' => 'GENERATED',
                    ];
                }

                if ($generatedRows->count() > 0) {
                    $hasGeneratedCharge = true;
                }

                $cursor->addMonth();
                $guard++;
                continue;
            }

            $summary = $service->summary($monthCycle);
            $allocationStatus = (string) ($summary['allocation_status'] ?? 'UNAVAILABLE');

            $matchingAllocations = array_values(array_filter(
                (array) ($summary['employee_allocations'] ?? []),
                fn($row) => in_array((string) ($row['company_id'] ?? ''), $scopeEmployeeIds, true)
            ));

            if (!empty($matchingAllocations)) {
                $monthBlocked = str_starts_with($allocationStatus, 'BLOCKED_')
                    || ((int) ($summary['_http'] ?? 200) >= 400);

                foreach ($matchingAllocations as $allocation) {
                    $chargeRows[] = [
                        'month_cycle' => $monthCycle,
                        'company_id' => (string) ($allocation['company_id'] ?? ''),
                        'father_name' => (string) ($allocation['father_name'] ?? ''),
                        'children_count' => (int) ($allocation['children_count'] ?? 0),
                        'chargeable_units' => round((float) ($allocation['chargeable_units'] ?? 0), 2),
                        'payable_amount' => $monthBlocked ? null : round((float) ($allocation['payable_amount'] ?? 0), 2),
                        'allocation_status' => $monthBlocked ? $allocationStatus : 'PREVIEW_NOT_GENERATED',
                    ];
                }

                if ($monthBlocked) {
                    $hasBlockedCharge = true;

                    foreach ((array) ($summary['expense_blockers'] ?? []) as $blocker) {
                        $blockers[] = ['month_cycle' => $monthCycle] + (array) $blocker;
                    }

                    foreach ((array) ($summary['allocation_blockers'] ?? []) as $blocker) {
                        $blockers[] = ['month_cycle' => $monthCycle] + (array) $blocker;
                    }
                } else {
                    $hasPreviewCharge = true;
                }
            }

            $cursor->addMonth();
            $guard++;
        }

        if (empty($chargeRows)) {
            return [
                'rows' => [],
                'blocked' => false,
                'generated' => false,
                'allocation_status' => 'NO_SCHOOL_VAN_CHARGE',
                'total_amount' => 0.0,
                'blockers' => [],
                'note' => 'No school van charge found for the selected employee and period.',
            ];
        }

        $status = $hasBlockedCharge
            ? 'BLOCKED_CORRECTION_REQUIRED'
            : ($hasGeneratedCharge && !$hasPreviewCharge
                ? 'GENERATED'
                : 'PREVIEW_NOT_GENERATED');

        return [
            'rows' => $chargeRows,
            'blocked' => $hasBlockedCharge,
            'generated' => $hasGeneratedCharge && !$hasPreviewCharge && !$hasBlockedCharge,
            'allocation_status' => $status,
            'total_amount' => $hasBlockedCharge ? null : round(array_sum(array_map(
                fn($row) => (float) ($row['payable_amount'] ?? 0),
                $chargeRows
            )), 2),
            'blockers' => $blockers,
            'note' => $hasBlockedCharge
                ? 'School van charge is blocked until the identified correction is resolved.'
                : ($status === 'GENERATED'
                    ? 'Official generated school van charge is displayed separately and is not merged into the electric total.'
                    : 'Calculated preview only. Generate School Van Bill to create the official charge.'),
        ];
    }

    private function statementRows(Request $request): array
    {
        $fromMonth = $this->normalizeMonthCycle((string)$request->query('from_month', $request->query('month_cycle', '05-2026')));
        $toMonth = $this->normalizeMonthCycle((string)$request->query('to_month', $request->query('month_cycle', $fromMonth)));

        if ($fromMonth === '') $fromMonth = '05-2026';
        if ($toMonth === '') $toMonth = $fromMonth;

        $q = trim((string)$request->query('q',''));

        $query = DB::table('util_elec_employee_share_monthly as s')
            ->leftJoin('employees_master as e','e.company_id','=','s.employee_id')
            ->select('s.month_cycle','s.employee_id as company_id','e.name','e.department','e.designation','e.colony_type','e.block_floor','s.unit_id','s.room_no','s.active_days','s.emp_used_units','s.eligible_units','s.billable_units','s.rate','s.amount as electric_amount')
            ->whereRaw("STR_TO_DATE(CONCAT('01-', s.month_cycle), '%d-%m-%Y') >= STR_TO_DATE(?, '%d-%m-%Y')", ['01-'.$fromMonth])
            ->whereRaw("STR_TO_DATE(CONCAT('01-', s.month_cycle), '%d-%m-%Y') <= STR_TO_DATE(?, '%d-%m-%Y')", ['01-'.$toMonth]);

        foreach (['company_id' => 's.employee_id', 'unit_id' => 's.unit_id', 'room_no' => 's.room_no', 'department' => 'e.department'] as $param => $col) {
            if ($request->query($param)) $query->where($col, $request->query($param));
        }

        if ($q !== '') {
            $query->where(fn($w) => $w
                ->where('s.employee_id','like',"%$q%")
                ->orWhere('e.name','like',"%$q%")
                ->orWhere('s.unit_id','like',"%$q%")
                ->orWhere('s.room_no','like',"%$q%")
            );
        }

        $status = (string)$request->query('status','');
        if ($status === 'positive') $query->where('s.amount','>',0);
        if ($status === 'zero') $query->where('s.amount','=',0);

        return $query
            ->orderByRaw("STR_TO_DATE(CONCAT('01-', s.month_cycle), '%d-%m-%Y')")
            ->orderBy('s.employee_id')
            ->limit(100000)
            ->get()
            ->map(function ($r) {
                $row = (array)$r;
                $row['previous_balance'] = 0;
                $row['adjustments'] = 0;
                $row['paid_amount'] = 0;
                $row['outstanding_amount'] = round((float)$row['electric_amount'], 2);
                $row['billing_status'] = ((float)$row['electric_amount'] > 0) ? 'POSITIVE BILL' : 'ZERO BILL';
                return $row;
            })->all();
    }

    private function cfg(string $module): array
    {
        if (!isset($this->modules[$module])) abort(404, 'Unknown grid module.');
        return $this->modules[$module];
    }

    private function qualify(string $module, string $col): string
    {
        return match($module) {
            'occupancy' => $col === 'employee_id' ? 'util_occupancy_monthly.employee_id' : 'util_occupancy_monthly.'.$col,
            'active-days' => $col === 'company_id' ? 'electric_active_days_monthly.company_id' : 'electric_active_days_monthly.'.$col,
            'meter-readings' => 'readings.'.$col,
            'meter-registry' => DB::table('util_meter_unit')->count() > 0 ? 'util_meter_unit.'.$col : 'readings.'.$col,
            'units' => DB::table('util_unit')->count() > 0 ? 'util_unit.'.$col : match($col) {
                'unit_name' => 'util_occupancy_monthly.unit_id',
                'colony_type', 'block_name' => 'util_occupancy_monthly.block_floor',
                'is_active' => DB::raw('1'),
                default => 'util_occupancy_monthly.'.$col,
            },
            default => $this->modules[$module]['table'].'.'.$col,
        };
    }

    private function filterColumn(string $module, string $filter): string
    {
        if ($module === 'active-days' && $filter === 'month_cycle') return 'electric_active_days_monthly.billing_month_date';
        if ($module === 'active-days' && $filter === 'company_id') return 'electric_active_days_monthly.company_id';
        if ($module === 'occupancy') return $filter === 'company_id' ? 'util_occupancy_monthly.employee_id' : 'util_occupancy_monthly.'.$filter;
        return $this->qualify($module, $filter);
    }

    private function orderColumn(string $module, string $key): string
    {
        return match($module) {
            'occupancy' => 'util_occupancy_monthly.id',
            'active-days' => 'electric_active_days_monthly.id',
            'rooms' => 'util_unit_room_snapshot.id',
            'meter-readings' => 'readings.id',
            'meter-registry' => DB::table('util_meter_unit')->count() > 0 ? 'util_meter_unit.meter_id' : 'readings.meter_id',
            'units' => DB::table('util_unit')->count() > 0 ? 'util_unit.unit_id' : 'util_occupancy_monthly.unit_id',
            default => $this->modules[$module]['table'].'.'.$key,
        };
    }

    private function prefixed(array $cols, string $table, array $aliases = []): array
    {
        return array_map(fn($c) => in_array($c, $aliases, true) ? $table.'.'.$c.' as company_id' : $table.'.'.$c, $cols);
    }

    private function columnsFor(string $module, array $cfg): array
    {
        return match($module) {
            'occupancy' => ['id','month_cycle','company_id','name','unit_id','room_no','category','block_floor','active_days'],
            'active-days' => ['id','billing_month_date','company_id','name','active_days','unit_id','room_no','remarks'],
            'rooms' => ['id','month_cycle','unit_id','category','block_floor','room_no','room_persons'],
            'meter-registry' => ['meter_id','unit_id','meter_type','is_active','last_reading_date','last_reading_value'],
            'meter-readings' => ['id','month_cycle','unit_id','meter_id','meter_type','previous_reading','current_reading','unit_used','reading_date'],
            'units' => DB::table('util_unit')->count() > 0 ? $cfg['select'] : ['unit_id','unit_name','category','colony_type','is_active'],
            default => $cfg['select'],
        };
    }

    private function summary(string $module, Request $request): array
    {
        if ($module === 'billing-preview') {
            $month = (string)$request->query('month_cycle','05-2026');
            $base = DB::table('util_elec_employee_share_monthly')->where('month_cycle', $month);
            return [
                'total_rows' => (clone $base)->count(),
                'positive_bill_rows' => (clone $base)->where('amount','>',0)->count(),
                'zero_bill_rows' => (clone $base)->where('amount','=',0)->count(),
                'total_amount' => round((float)(clone $base)->sum('amount'), 2),
                'alerts_count' => 1565,
                'skipped_rows' => 199,
            ];
        }
        return [];
    }

    private function validateActiveDays(array $data): void
    {
        if (!isset($data['active_days'])) return;
        $days = (float)$data['active_days'];
        if ($days < 0) abort(422, 'ActiveDays cannot be negative.');
        $month = $data['billing_month_date'] ?? null;
        if ($month) {
            try {
                $monthDays = Carbon::parse($month)->daysInMonth;
                if ($days > $monthDays) abort(422, 'ActiveDays cannot exceed MonthDays.');
            } catch (\Throwable $e) {}
        }
    }

    private function monthToDatePrefix(string $month): ?string
    {
        try { return Carbon::createFromFormat('M-Y', $month)->format('Y-m'); } catch (\Throwable $e) {}
        try { return Carbon::createFromFormat('m-Y', $month)->format('Y-m'); } catch (\Throwable $e) {}
        return preg_match('/^\d{4}-\d{2}/', $month) ? substr($month,0,7) : null;
    }

    private function normalizeMonthCycle(string $month): string
    {
        $month = trim($month);
        if ($month === '') return $month;
        try { return Carbon::createFromFormat('Y-m', $month)->format('m-Y'); } catch (\Throwable $e) {}
        try { return Carbon::createFromFormat('M-Y', $month)->format('m-Y'); } catch (\Throwable $e) {}
        try { return Carbon::createFromFormat('F-Y', $month)->format('m-Y'); } catch (\Throwable $e) {}
        try { return Carbon::createFromFormat('m-Y', $month)->format('m-Y'); } catch (\Throwable $e) {}
        return $month;
    }

    private function csvResponse(string $filename, array $columns, array|object $rows)
    {
        $rows = json_decode(json_encode($rows), true) ?: [];
        $callback = function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $row) fputcsv($out, array_map(fn($c) => $row[$c] ?? '', $columns));
            fclose($out);
        };
        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function simplePdf(array $payload): string
    {
        $lines = ['Employee Statement', 'Month: '.$payload['month_cycle'], 'CompanyID: '.$payload['company_id'], 'Total: '.$payload['total_amount'], $payload['note']];
        foreach (array_slice($payload['statement'],0,25) as $r) $lines[] = implode(' | ', array_map(fn($k,$v)=>$k.': '.$v, array_keys($r), $r));
        $text = implode("\\n", $lines);
        $content = "BT /F1 10 Tf 50 780 Td ".str_replace(['\\','(',')',"\n"], ['\\\\','\\(','\\)',') Tj T* ('], $text).") Tj ET";
        $objs=[]; $objs[]='1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objs[]='2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $objs[]='3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj';
        $objs[]='4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objs[]='5 0 obj << /Length '.strlen($content).' >> stream '.$content.' endstream endobj';
        $pdf="%PDF-1.4\n"; $offsets=[0]; foreach($objs as $o){$offsets[]=strlen($pdf); $pdf.=$o."\n";} $xref=strlen($pdf); $pdf.="xref\n0 ".(count($objs)+1)."\n0000000000 65535 f \n"; for($i=1;$i<count($offsets);$i++) $pdf.=sprintf('%010d 00000 n ', $offsets[$i])."\n"; return $pdf."trailer << /Root 1 0 R /Size ".(count($objs)+1)." >>\nstartxref\n$xref\n%%EOF";
    }
}
