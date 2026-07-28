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
    public function employeeMaster()
    {
        $tree = [];
        $rows = \Illuminate\Support\Facades\DB::table('util_unit as u')
            ->join('util_unit_rooms as r', 'r.unit_id', '=', 'u.unit_id')
            ->where('u.is_active', 1)->where('r.is_active', 1)
            ->whereNotNull('u.colony_type')
            ->select('u.colony_type', 'u.block_name', 'u.unit_id', 'r.room_no')
            ->orderBy('u.colony_type')->orderBy('u.block_name')->orderBy('r.room_no')
            ->get();
        $occ = \Illuminate\Support\Facades\DB::table('electric_v1_occupancy')
            ->select('unit_id', 'room_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT company_id) c'))
            ->groupBy('unit_id', 'room_id')->get()
            ->mapWithKeys(fn($r) => [$r->unit_id.'|'.$r->room_id => $r->c]);
        foreach ($rows as $r) {
            $tree[$r->colony_type][$r->block_name ?: '—'][] = [
                'unit' => $r->unit_id, 'room' => $r->room_no,
                'n' => (int) ($occ[$r->unit_id.'|'.$r->room_no] ?? 0),
            ];
        }
        $tree['Outside Colony'] = ['—' => [['unit' => 'OUTSIDE', 'room' => 'Outside Colony', 'n' => 0]]];

        $DB = \Illuminate\Support\Facades\DB::class;
        $noResidence = \Illuminate\Support\Facades\DB::table('employees_master')
            ->where('active', 'Yes')
            ->where(function ($w) {
                $w->whereNull('residence_status')->orWhere('residence_status', 'UNASSIGNED');
            })
            ->select('company_id', 'name', 'department', 'designation')
            ->orderBy('company_id')->get();

        $formPending = \Illuminate\Support\Facades\DB::table('employees_master')
            ->where('active', 'Yes')->where('residence_status', 'FORM_PENDING')
            ->select('company_id', 'name', 'department', 'designation')
            ->orderBy('company_id')->get();

        $outsideEmployees = \Illuminate\Support\Facades\DB::table('employees_master')
            ->where('active', 'Yes')->where('residence_status', 'OUTSIDE')
            ->select('company_id', 'name', 'department', 'designation', 'updated_at')
            ->orderBy('company_id')->get()
            ->map(function ($r) {
                $r->marked_on = $r->updated_at ? substr((string) $r->updated_at, 0, 10) : '';
                return $r;
            });

        $exportRows = ['no_residence' => [], 'outside' => []];
        foreach ($noResidence as $r) {
            $exportRows['no_residence'][] = ['="'.$r->company_id.'"', (string) $r->name, (string) ($r->department ?? ''), (string) ($r->designation ?? '')];
        }
        $exportRows['form_pending'] = [];
        foreach ($formPending as $r) {
            $exportRows['form_pending'][] = ['="'.$r->company_id.'"', (string) $r->name, (string) ($r->department ?? ''), (string) ($r->designation ?? '')];
        }
        foreach ($outsideEmployees as $r) {
            $exportRows['outside'][] = ['="'.$r->company_id.'"', (string) $r->name, (string) ($r->department ?? ''), (string) ($r->designation ?? ''), (string) $r->marked_on];
        }

        $crowded = \Illuminate\Support\Facades\DB::table('electric_v1_occupancy')
            ->select('unit_id', 'room_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT company_id) people'))
            ->groupBy('unit_id', 'room_id')
            ->havingRaw('COUNT(DISTINCT company_id) > 1')
            ->orderByDesc('people')->limit(40)->get();

        $empTotal    = \Illuminate\Support\Facades\DB::table('employees_master')->count();
        $empActive   = \Illuminate\Support\Facades\DB::table('employees_master')->where('active', 'Yes')->count();
        $empInactive = \Illuminate\Support\Facades\DB::table('employees_master')->where('active', 'No')->count();
        $empMissing  = \Illuminate\Support\Facades\DB::table('employees_master')
            ->where(function ($w) { $w->whereNull('active')->orWhereRaw("TRIM(active) = ''"); })->count();

        return view('ui.employee-master', [
            'empTotal' => $empTotal,
            'empActive' => $empActive,
            'empInactive' => $empInactive,
            'empMissing' => $empMissing,
            'tree' => $tree,
            'noResidence' => $noResidence,
            'outsideEmployees' => $outsideEmployees,
            'exportRows' => $exportRows,
            'formPending' => $formPending,
            'crowded' => $crowded,
        ]);
    }
    public function employees() { return view('ui.employees'); }
    public function employeeHelper() { return view('ui.employee-helper'); }
    public function unitMaster(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $colony = trim((string) $request->query('colony', ''));
        $type = strtoupper(trim((string) $request->query('type', '')));

        $query = \Illuminate\Support\Facades\DB::table('util_unit');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('unit_id', 'like', '%'.$q.'%')
                  ->orWhere('room_no', 'like', '%'.$q.'%')
                  ->orWhere('block_name', 'like', '%'.$q.'%');
            });
        }
        if ($colony !== '') { $query->where('colony_type', $colony); }

        $matchAllRooms = static function ($query, callable $match): void {
            $query->whereExists(function ($exists) {
                $exists->selectRaw('1')
                    ->from('util_unit_rooms as r_any')
                    ->whereColumn('r_any.unit_id', 'util_unit.unit_id')
                    ->where('r_any.is_active', 1);
            })->whereNotExists(function ($not) use ($match) {
                $not->selectRaw('1')
                    ->from('util_unit_rooms as r_bad')
                    ->whereColumn('r_bad.unit_id', 'util_unit.unit_id')
                    ->where('r_bad.is_active', 1)
                    ->where(function ($w) use ($match) {
                        $match($w, 'r_bad', true);
                    });
            });
        };

        if ($type !== '') {
            if ($type === 'BACHELOR') {
                $matchAllRooms($query, fn($w, $alias, $negated = false) => $w->where($alias.'.occupant_grade', '<>', 'BACHELOR')->orWhereNull($alias.'.occupant_grade'));
            } elseif ($type === 'HOSTEL') {
                $matchAllRooms($query, fn($w, $alias, $negated = false) => $w->where($alias.'.occupant_grade', '<>', 'SENIOR_STAFF')->orWhereNull($alias.'.occupant_grade'));
            } elseif ($type === 'CONTAINER') {
                $matchAllRooms($query, fn($w, $alias, $negated = false) => $w->where($alias.'.residence_type', '<>', 'CONTAINER')->orWhereNull($alias.'.residence_type'));
            } elseif ($type === 'HOUSE') {
                $houseTypes = ['HOUSE_A+', 'HOUSE_A', 'HOUSE_B', 'HOUSE_C'];
                $matchAllRooms($query, fn($w, $alias, $negated = false) => $w->whereNotIn($alias.'.residence_type', $houseTypes)->orWhereNull($alias.'.residence_type'));
            } elseif ($type === 'COMMON') {
                $matchAllRooms($query, fn($w, $alias, $negated = false) => $w->where(function ($x) use ($alias) {
                    $x->where($alias.'.residence_type', '<>', 'COMMON')->orWhereNull($alias.'.residence_type');
                })->where(function ($x) use ($alias) {
                    $x->where($alias.'.occupant_grade', '<>', 'COMMON')->orWhereNull($alias.'.occupant_grade');
                }));
            } elseif ($type === 'UNSET') {
                $query->whereExists(function ($exists) {
                    $exists->selectRaw('1')
                        ->from('util_unit_rooms as r_unset')
                        ->whereColumn('r_unset.unit_id', 'util_unit.unit_id')
                        ->where('r_unset.is_active', 1)
                        ->where(function ($w) {
                            $w->whereNull('r_unset.residence_type')
                              ->orWhereNull('r_unset.occupant_grade');
                        });
                });
            }
        }

        $units = $query->orderBy('unit_id')->get();

        $occ = \Illuminate\Support\Facades\DB::table('electric_v1_occupancy')
            ->select('unit_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT company_id) as c'))
            ->groupBy('unit_id')->pluck('c', 'unit_id');

        $colonies = \Illuminate\Support\Facades\DB::table('util_unit')
            ->whereNotNull('colony_type')->distinct()->orderBy('colony_type')->pluck('colony_type');

        $typeStats = \Illuminate\Support\Facades\DB::select("\n            SELECT type, COUNT(*) total,\n                   SUM(CASE WHEN occ_count IS NULL OR occ_count=0 THEN 1 ELSE 0 END) vacant,\n                   SUM(CASE WHEN occ_count>0 THEN 1 ELSE 0 END) occupied\n            FROM (\n                SELECT u.unit_id, o.c occ_count,\n                       CASE\n                         WHEN SUM(CASE WHEN r.residence_type IS NULL OR r.occupant_grade IS NULL THEN 1 ELSE 0 END) > 0 THEN 'UNSET'\n                         WHEN COUNT(r.id) > 0 AND SUM(CASE WHEN r.occupant_grade = 'BACHELOR' THEN 1 ELSE 0 END) = COUNT(r.id) THEN 'BACHELOR'\n                         WHEN COUNT(r.id) > 0 AND SUM(CASE WHEN r.occupant_grade = 'SENIOR_STAFF' THEN 1 ELSE 0 END) = COUNT(r.id) THEN 'HOSTEL'\n                         WHEN COUNT(r.id) > 0 AND SUM(CASE WHEN r.residence_type = 'CONTAINER' THEN 1 ELSE 0 END) = COUNT(r.id) THEN 'CONTAINER'\n                         WHEN COUNT(r.id) > 0 AND SUM(CASE WHEN r.residence_type IN ('HOUSE_A+', 'HOUSE_A', 'HOUSE_B', 'HOUSE_C') THEN 1 ELSE 0 END) = COUNT(r.id) THEN 'HOUSE'\n                         WHEN COUNT(r.id) > 0 AND SUM(CASE WHEN r.residence_type = 'COMMON' OR r.occupant_grade = 'COMMON' THEN 1 ELSE 0 END) = COUNT(r.id) THEN 'COMMON'\n                         ELSE NULL\n                       END AS type\n                FROM util_unit u\n                LEFT JOIN util_unit_rooms r ON r.unit_id=u.unit_id AND r.is_active=1\n                LEFT JOIN (SELECT unit_id, COUNT(DISTINCT company_id) c FROM electric_v1_occupancy GROUP BY unit_id) o\n                  ON o.unit_id=u.unit_id\n                WHERE u.is_active=1\n                GROUP BY u.unit_id, o.c\n            ) typed\n            WHERE type IS NOT NULL\n            GROUP BY type ORDER BY total DESC");

        return view('ui.unit-master', [
            'roomEmployees' => \Illuminate\Support\Facades\DB::table('electric_v1_occupancy as o')
                ->leftJoin('employees_master as e', 'e.company_id', '=', 'o.company_id')
                ->select('o.unit_id', 'o.room_id', 'o.company_id', 'e.name', 'e.department', 'e.designation', 'e.mobile_no', 'e.active')
                ->get()->groupBy(fn($r) => $r->unit_id.'|'.$r->room_id),
            'rooms' => \Illuminate\Support\Facades\DB::table('util_unit_rooms')->orderBy('room_no')->get()->groupBy('unit_id'),
            'roomOcc' => \Illuminate\Support\Facades\DB::table('electric_v1_occupancy')
                ->select('unit_id', 'room_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT company_id) as c'))
                ->groupBy('unit_id', 'room_id')->get()
                ->mapWithKeys(fn($r) => [$r->unit_id.'|'.$r->room_id => $r->c]),
            'typeStats' => $typeStats,
            'type' => $type,
            'units' => $units,
            'occ' => $occ,
            'colonies' => $colonies,
            'q' => $q,
            'colony' => $colony,
            'totalUnits' => $units->count(),
        ]);
    }

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

        $houseCascade = \Illuminate\Support\Facades\DB::table('util_unit_rooms')
            ->where('is_active', 1)->whereNotNull('residence_type')
            ->orderBy('residence_type')->orderBy('floor')->orderBy('room_no')
            ->get(['residence_type', 'floor', 'room_no']);
        $cascadeMap = [];
        foreach ($houseCascade as $hc) {
            $ht = (string) $hc->residence_type; $fl = (string) ($hc->floor ?? '');
            $cascadeMap[$ht][$fl][] = (string) $hc->room_no;
        }
        $deptList = ['SPINNING', 'WEAVING', 'CENTRALIZED'];
        $schoolList = \Illuminate\Support\Facades\DB::table('family_members')
            ->where('is_active', 1)->whereNotNull('school_name')->where('school_name', '<>', '')
            ->distinct()->orderBy('school_name')->pluck('school_name')->all();

        return view('ui.family-list', [
            'cascadeMap' => $cascadeMap,
            'deptList' => $deptList,
            'schoolList' => $schoolList,
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

    // Read-only analysis data for Meter Readings page. No writes, no billing side effects.
    public function meterReadingsAnalysisData(Request $request)
    {
        $schema = app('db')->connection()->getSchemaBuilder();
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        $departmentFilter = strtolower(trim((string) $request->query('department', '')));
        $buildingFilter = strtolower(trim((string) $request->query('building', '')));
        $unitFilter = strtolower(trim((string) $request->query('unit_id', '')));
        $roomFilter = strtolower(trim((string) $request->query('room_no', '')));

        $hasUtilReadings = $schema->hasTable('util_meter_readings');
        $hasLegacyReadings = $schema->hasTable('readings');
        $source = $hasUtilReadings ? 'util_meter_readings' : ($hasLegacyReadings ? 'readings' : null);

        $unitMeta = [];
        if ($schema->hasTable('util_unit')) {
            foreach (\Illuminate\Support\Facades\DB::table('util_unit')->get() as $u) {
                $uid = trim((string) $u->unit_id);
                if ($uid === '') continue;
                $unitMeta[$uid] = [
                    'building' => (string) ($u->colony_type ?: $u->block_name ?: ''),
                    'block' => (string) ($u->block_name ?: ''),
                    'room' => (string) ($u->room_no ?: ''),
                ];
            }
        }

        $employeeByUnit = [];
        if ($schema->hasTable('employees_master')) {
            $employees = \Illuminate\Support\Facades\DB::table('employees_master')
                ->select('unit_id', 'department', 'colony_type', 'block_floor', 'room_no', 'active')
                ->whereNotNull('unit_id')
                ->orderByRaw("CASE WHEN active='Yes' THEN 0 ELSE 1 END")
                ->limit(50000)
                ->get();
            foreach ($employees as $e) {
                $uid = trim((string) $e->unit_id);
                if ($uid === '') continue;
                $current = $employeeByUnit[$uid] ?? null;
                $candidate = [
                    'department' => (string) ($e->department ?: ''),
                    'building' => (string) ($e->colony_type ?: $e->block_floor ?: ''),
                    'room' => (string) ($e->room_no ?: ''),
                    'active' => (string) ($e->active ?: ''),
                ];
                if (!$current || strtolower($candidate['active']) === 'yes') $employeeByUnit[$uid] = $candidate;
            }
        }

        $meterByUnit = [];
        $allUnits = [];
        if ($schema->hasTable('util_meter_unit')) {
            foreach (\Illuminate\Support\Facades\DB::table('util_meter_unit')->select('meter_id', 'unit_id', 'meter_type', 'is_active')->limit(50000)->get() as $m) {
                $uid = trim((string) $m->unit_id);
                if ($uid === '') continue;
                $allUnits[$uid] = true;
                $meterByUnit[$uid][] = [
                    'meter_id' => (string) ($m->meter_id ?: ''),
                    'meter_type' => (string) ($m->meter_type ?: ''),
                    'is_active' => (string) ($m->is_active ?? ''),
                ];
            }
        }
        foreach (array_keys($unitMeta) as $uid) $allUnits[$uid] = true;
        foreach (array_keys($employeeByUnit) as $uid) $allUnits[$uid] = true;

        $optionRows = [];
        foreach (array_keys($allUnits) as $uid) {
            $meta = $this->meterAnalysisMeta($uid, $unitMeta, $employeeByUnit);
            $optionRows[] = $this->meterAnalysisRow($meta, [
                'meter_id' => $meterByUnit[$uid][0]['meter_id'] ?? '',
                'unit_id' => $uid,
                'source' => 'master_mapping',
                'opening_date' => null,
                'closing_date' => null,
                'opening_reading' => null,
                'closing_reading' => null,
                'consumption' => 0,
                'reading_status' => 'Mapping only',
            ]);
        }

        $rows = [];
        if ($hasUtilReadings) {
            $q = \Illuminate\Support\Facades\DB::table('util_meter_readings')
                ->select('meter_id', 'unit_id', 'reading_date', 'reading_value')
                ->orderBy('meter_id')
                ->orderBy('reading_date');
            if ($to !== '') $q->whereDate('reading_date', '<=', $to);
            $raw = $q->limit(50000)->get();
            $grouped = [];
            foreach ($raw as $r) {
                $key = (string) ($r->meter_id ?: $r->unit_id);
                if ($key === '') continue;
                $grouped[$key][] = $r;
            }
            foreach ($grouped as $meterKey => $items) {
                $opening = null; $closing = null; $firstInRange = null; $lastInRange = null;
                foreach ($items as $r) {
                    $d = (string) $r->reading_date;
                    if ($from === '' || $d >= $from) {
                        $firstInRange ??= $r;
                        $lastInRange = $r;
                    }
                    if ($from !== '' && $d <= $from) $opening = $r;
                    if ($to === '' || $d <= $to) $closing = $r;
                }
                $opening = $opening ?: $firstInRange;
                $closing = $closing ?: $lastInRange;
                if (!$opening || !$closing) continue;
                $unitId = trim((string) ($closing->unit_id ?: $opening->unit_id ?: ''));
                if ($unitId !== '') $allUnits[$unitId] = true;
                $meta = $this->meterAnalysisMeta($unitId, $unitMeta, $employeeByUnit);
                $consumption = round((float) $closing->reading_value - (float) $opening->reading_value, 3);
                $rows[] = $this->meterAnalysisRow($meta, [
                    'meter_id' => (string) ($closing->meter_id ?: $opening->meter_id),
                    'unit_id' => $unitId,
                    'source' => 'util_meter_readings',
                    'opening_date' => (string) $opening->reading_date,
                    'closing_date' => (string) $closing->reading_date,
                    'opening_reading' => (float) $opening->reading_value,
                    'closing_reading' => (float) $closing->reading_value,
                    'consumption' => max(0, $consumption),
                    'reading_status' => 'OK',
                ]);
            }
        } elseif ($hasLegacyReadings) {
            $q = \Illuminate\Support\Facades\DB::table('readings')
                ->select('meter_id', 'unit_id', 'meter_type', 'month_cycle', 'usage', 'amount')
                ->orderBy('month_cycle');
            if ($from !== '') $q->where('month_cycle', '>=', substr($from, 0, 7));
            if ($to !== '') $q->where('month_cycle', '<=', substr($to, 0, 7));
            $raw = $q->limit(50000)->get();
            foreach ($raw as $r) {
                $unitId = trim((string) ($r->unit_id ?: ''));
                if ($unitId !== '') $allUnits[$unitId] = true;
                $meta = $this->meterAnalysisMeta($unitId, $unitMeta, $employeeByUnit);
                $rows[] = $this->meterAnalysisRow($meta, [
                    'meter_id' => (string) ($r->meter_id ?: ''),
                    'unit_id' => $unitId,
                    'source' => 'readings',
                    'opening_date' => (string) $r->month_cycle,
                    'closing_date' => (string) $r->month_cycle,
                    'opening_reading' => null,
                    'closing_reading' => null,
                    'consumption' => round((float) $r->usage, 3),
                    'reading_status' => 'OK',
                ]);
            }
        }

        $seenUnits = [];
        foreach ($rows as $r) if (($r['unit_id'] ?? '') !== '') $seenUnits[$r['unit_id']] = true;
        foreach (array_keys($allUnits) as $uid) {
            if (isset($seenUnits[$uid])) continue;
            $meta = $this->meterAnalysisMeta($uid, $unitMeta, $employeeByUnit);
            $rows[] = $this->meterAnalysisRow($meta, [
                'meter_id' => $meterByUnit[$uid][0]['meter_id'] ?? '',
                'unit_id' => $uid,
                'source' => 'master_mapping',
                'opening_date' => null,
                'closing_date' => null,
                'opening_reading' => null,
                'closing_reading' => null,
                'consumption' => 0,
                'reading_status' => 'Missing reading',
            ]);
        }

        $filterFn = function ($r) use ($departmentFilter, $buildingFilter, $unitFilter, $roomFilter) {
            if ($departmentFilter !== '' && strtolower($r['department']) !== $departmentFilter) return false;
            if ($buildingFilter !== '' && strtolower($r['building']) !== $buildingFilter) return false;
            if ($unitFilter !== '' && strtolower($r['unit_id']) !== $unitFilter) return false;
            if ($roomFilter !== '' && strtolower($r['room_no']) !== $roomFilter) return false;
            return true;
        };
        $rows = array_values(array_filter($rows, $filterFn));
        usort($rows, fn($a, $b) => [$a['department'], $a['building'], $a['unit_id'], $a['room_no']] <=> [$b['department'], $b['building'], $b['unit_id'], $b['room_no']]);
        usort($optionRows, fn($a, $b) => [$a['department'], $a['building'], $a['unit_id'], $a['room_no']] <=> [$b['department'], $b['building'], $b['unit_id'], $b['room_no']]);

        $total = array_sum(array_column($rows, 'consumption'));
        $departments = array_values(array_unique(array_filter(array_map(fn($r) => $r['department'], array_merge($rows, $optionRows)))));
        $buildings = array_values(array_unique(array_filter(array_map(fn($r) => $r['building'], $optionRows))));

        return response()->json([
            'status' => 'ok',
            'source' => $source,
            'summary' => [
                'meters' => count($rows),
                'total_consumption' => round($total, 3),
                'unmapped' => count(array_filter($rows, fn($r) => $r['department'] === 'Unmapped' || $r['building'] === 'Unmapped')),
                'missing_readings' => count(array_filter($rows, fn($r) => ($r['reading_status'] ?? '') === 'Missing reading')),
            ],
            'departments' => array_values(array_unique(array_merge(['Weaving', 'Spinning', 'Centralized'], $departments))),
            'buildings' => $buildings,
            'options' => ['rows' => array_slice($optionRows, 0, 5000)],
            'rows' => array_slice($rows, 0, 2000),
        ]);
    }

    private function meterAnalysisMeta(string $unitId, array $unitMeta, array $employeeByUnit): array
    {
        $u = $unitMeta[$unitId] ?? [];
        $e = $employeeByUnit[$unitId] ?? [];
        $building = trim((string) (($e['building'] ?? '') ?: ($u['building'] ?? '') ?: ($u['block'] ?? '')));
        $room = trim((string) (($e['room'] ?? '') ?: ($u['room'] ?? '')));
        $department = trim((string) ($e['department'] ?? ''));
        $haystack = strtolower($department.' '.$building.' '.$unitId.' '.$room);
        if ($department === '') {
            if (str_contains($haystack, 'weav')) $department = 'Weaving';
            elseif (str_contains($haystack, 'spin')) $department = 'Spinning';
            elseif (str_contains($haystack, 'central')) $department = 'Centralized';
            else $department = 'Unmapped';
        } else {
            $low = strtolower($department);
            if (str_contains($low, 'weav')) $department = 'Weaving';
            elseif (str_contains($low, 'spin')) $department = 'Spinning';
            elseif (str_contains($low, 'central')) $department = 'Centralized';
        }
        return [
            'department' => $department,
            'building' => $building !== '' ? $building : 'Unmapped',
            'room_no' => $room !== '' ? $room : 'Unmapped',
        ];
    }

    private function meterAnalysisRow(array $meta, array $row): array
    {
        return array_merge([
            'department' => $meta['department'],
            'building' => $meta['building'],
            'room_no' => $meta['room_no'],
        ], $row);
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
