<?php

namespace App\Services\Billing\V2;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class PeopleResidencyService
{
    private const EMPLOYEE_MAP = [
        'CompanyID' => 'company_id', 'company_id' => 'company_id',
        'Name' => 'name', 'name' => 'name',
        "Father's Name" => 'father_name', 'father_name' => 'father_name',
        'CNIC_No.' => 'cnic_no', 'cnic_no' => 'cnic_no',
        'Mobile_No.' => 'mobile_no', 'mobile_no' => 'mobile_no',
        'Department' => 'department', 'department' => 'department',
        'Section' => 'section', 'section' => 'section',
        'Sub Section' => 'sub_section', 'sub_section' => 'sub_section',
        'Designation' => 'designation', 'designation' => 'designation',
        'Employee Type' => 'employee_type', 'employee_type' => 'employee_type',
        'Join Date' => 'join_date', 'join_date' => 'join_date',
        'Leave Date' => 'leave_date', 'leave_date' => 'leave_date',
        'Unit_ID' => 'unit_id', 'unit_id' => 'unit_id',
        'Colony Type' => 'colony_type', 'colony_type' => 'colony_type',
        'Block Floor' => 'block_floor', 'block_floor' => 'block_floor',
        'Room No' => 'room_no', 'room_no' => 'room_no',
        'Shared Room' => 'shared_room', 'shared_room' => 'shared_room',
        'Active' => 'active', 'active' => 'active',
        'Remarks' => 'remarks', 'remarks' => 'remarks',
        'Iron Cot' => 'iron_cot', 'iron_cot' => 'iron_cot',
        'Single Bed' => 'single_bed', 'single_bed' => 'single_bed',
        'Double Bed' => 'double_bed', 'double_bed' => 'double_bed',
        'Mattress' => 'mattress', 'mattress' => 'mattress',
        'Sofa Set' => 'sofa_set', 'sofa_set' => 'sofa_set',
        'Bed Sheet' => 'bed_sheet', 'bed_sheet' => 'bed_sheet',
        'Wardrobe' => 'wardrobe', 'wardrobe' => 'wardrobe',
        'Centre Table' => 'centre_table', 'centre_table' => 'centre_table',
        'Wooden Chair' => 'wooden_chair', 'wooden_chair' => 'wooden_chair',
        'Dinning Table' => 'dinning_table', 'dinning_table' => 'dinning_table',
        'Dinning Chair' => 'dinning_chair', 'dinning_chair' => 'dinning_chair',
        'Side Table' => 'side_table', 'side_table' => 'side_table',
        'Fridge' => 'fridge', 'fridge' => 'fridge',
        'Water Dispenser' => 'water_dispenser', 'water_dispenser' => 'water_dispenser',
        'Washing Machine' => 'washing_machine', 'washing_machine' => 'washing_machine',
        'Air Cooler' => 'air_cooler', 'air_cooler' => 'air_cooler',
        'A/C' => 'ac', 'ac' => 'ac',
        'LED' => 'led', 'led' => 'led',
        'Gyser' => 'gyser', 'gyser' => 'gyser',
        'Electric Kettle' => 'electric_kettle', 'electric_kettle' => 'electric_kettle',
        'Wifi Rtr' => 'wifi_rtr', 'wifi_rtr' => 'wifi_rtr',
        'Water Bottle' => 'water_bottle', 'water_bottle' => 'water_bottle',
        'LPG cylinder' => 'lpg_cylinder', 'lpg_cylinder' => 'lpg_cylinder',
        'Gas Stove' => 'gas_stove', 'gas_stove' => 'gas_stove',
        'Crockery' => 'crockery', 'crockery' => 'crockery',
        'Kitchen Cabinet' => 'kitchen_cabinet', 'kitchen_cabinet' => 'kitchen_cabinet',
        'Mug' => 'mug', 'mug' => 'mug',
        'Bucket' => 'bucket', 'bucket' => 'bucket',
        'Mirror' => 'mirror', 'mirror' => 'mirror',
        'Dustbin' => 'dustbin', 'dustbin' => 'dustbin',
    ];

    private const EMPLOYEE_FIELDS = [
        'company_id','name','father_name','cnic_no','mobile_no','department','section','sub_section','designation','employee_type',
        'join_date','leave_date','unit_id','colony_type','block_floor','room_no','shared_room','active','remarks',
        'iron_cot','single_bed','double_bed','mattress','sofa_set','bed_sheet','wardrobe','centre_table','wooden_chair',
        'dinning_table','dinning_chair','side_table','fridge','water_dispenser','washing_machine','air_cooler','ac','led','gyser',
        'electric_kettle','wifi_rtr','water_bottle','lpg_cylinder','gas_stove','crockery','kitchen_cabinet','mug','bucket','mirror','dustbin',
    ];

    private const ASSET_LABELS = [
        'iron_cot' => 'Iron Cot', 'single_bed' => 'Single Bed', 'double_bed' => 'Double Bed', 'mattress' => 'Mattress',
        'sofa_set' => 'Sofa Set', 'bed_sheet' => 'Bed Sheet', 'wardrobe' => 'Wardrobe', 'centre_table' => 'Centre Table',
        'wooden_chair' => 'Wooden Chair', 'dinning_table' => 'Dinning Table', 'dinning_chair' => 'Dinning Chair',
        'side_table' => 'Side Table', 'fridge' => 'Fridge', 'water_dispenser' => 'Water Dispenser',
        'washing_machine' => 'Washing Machine', 'air_cooler' => 'Air Cooler', 'ac' => 'A/C', 'led' => 'LED',
        'gyser' => 'Gyser', 'electric_kettle' => 'Electric Kettle', 'wifi_rtr' => 'Wifi Router',
        'water_bottle' => 'Water Bottle', 'lpg_cylinder' => 'LPG Cylinder', 'gas_stove' => 'Gas Stove',
        'crockery' => 'Crockery', 'kitchen_cabinet' => 'Kitchen Cabinet', 'mug' => 'Mug', 'bucket' => 'Bucket',
        'mirror' => 'Mirror', 'dustbin' => 'Dustbin',
    ];

    public function employees(array $query = []): array
    {
        $q = trim((string) ($query['q'] ?? ''));
        $department = trim((string) ($query['department'] ?? ''));
        $active = trim((string) ($query['active'] ?? ''));
        $cycle = $this->latestCycle();
        $hrCycle = $this->hrSnapshotCycle($cycle);

        $snapshotIds = [];
        if ($hrCycle !== '' && Schema::hasTable('hr_active_employee_snapshots')) {
            $snapshotIds = DB::table('hr_active_employee_snapshots')
                ->where('month_cycle', $hrCycle)
                ->pluck('company_id')
                ->map(fn ($id) => (string) $id)
                ->flip()
                ->all();
        }

        $totalRows = 0;
        $rows = DB::table('employees_master')
            ->when($q !== '', function ($builder) use ($q) {
                $like = "%{$q}%";
                $builder->where(function ($w) use ($like) {
                    $w->where('company_id', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('cnic_no', 'like', $like)
                        ->orWhere('department', 'like', $like)
                        ->orWhere('designation', 'like', $like)
                        ->orWhere('unit_id', 'like', $like);
                });
            })
            ->when($department !== '', fn ($builder) => $builder->where('department', $department))
            ->when($active !== '', fn ($builder) => $builder->where('active', $active))
            ->orderBy('company_id')
            ->when(true, function ($b) use ($query, &$totalRows) {
                $totalRows = (clone $b)->count();
                $per = (int) ($query['per_page'] ?? 50);
                $page = max(1, (int) ($query['page'] ?? 1));
                if ($per > 0) { $b->limit($per)->offset(($page - 1) * $per); }
            })
            ->select([
                'company_id','name','father_name','cnic_no','mobile_no','department','section',
                'sub_section','designation','employee_type','join_date','leave_date','unit_id',
                'colony_type','block_floor','room_no','shared_room','active','remarks',
            ])
            ->get()
            ->map(function ($row) use ($snapshotIds, $cycle, $hrCycle) {
                $api = $this->toEmployeeRow((array) $row);
                $api['v2_month_cycle'] = $cycle;
                $api['v2_hr_snapshot_cycle'] = $hrCycle;
                $api['v2_hr_snapshot_status'] = isset($snapshotIds[(string) $row->company_id]) ? 'IN_SNAPSHOT' : 'NOT_IN_SNAPSHOT';
                return $api;
            })
            ->all();

        return [
            'status' => 'ok',
            'engine' => 'V2',
            'month_cycle' => $cycle,
            'hr_snapshot_cycle' => $hrCycle,
            'source' => ['employees_master', 'hr_active_employee_snapshots'],
            'rows' => $rows,
            'total' => $totalRows ?? count($rows),
            'page' => max(1, (int) ($query['page'] ?? 1)),
            'per_page' => (int) ($query['per_page'] ?? 50),
        ];
    }

    public function createEmployee(array $payload): array
    {
        $data = $this->normalizeEmployee($payload);
        $missing = $this->missing($data, ['company_id','name','cnic_no','department','designation','unit_id']);
        if ($missing !== []) {
            return $this->error('Missing mandatory fields: '.implode(', ', $missing), 422, ['missing_fields' => $missing]);
        }

        if (DB::table('employees_master')->where('company_id', $data['company_id'])->exists()) {
            return $this->error('CompanyID already exists.', 409);
        }

        DB::transaction(function () use ($data) {
            DB::table('employees_master')->insert($this->employeeWriteData($data, true));
        });

        // If a room was supplied at creation time, route it through the normal
        // assign flow so assignment history and electric occupancy rows are
        // created too. Writing employees_master alone leaves the employee out
        // of billing entirely.
        $residenceNote = '';
        $roomNo = trim((string) ($data['room_no'] ?? ''));
        $unitId = trim((string) ($data['unit_id'] ?? ''));
        if ($roomNo !== '' && $unitId !== '' && strtoupper($unitId) !== 'OUTSIDE') {
            $assign = $this->assignResidence($data['company_id'], [
                'unit_id' => $unitId,
                'room_no' => $roomNo,
                'effective_date' => $data['join_date'] ?? now()->toDateString(),
                'remarks' => 'Auto-assigned at employee creation',
            ]);
            if (($assign['status'] ?? '') !== 'ok') {
                $residenceNote = ' Residence not assigned: '.($assign['error'] ?? 'unknown error');
            }
        }

        return ['status' => 'ok', 'engine' => 'V2', 'company_id' => $data['company_id'], 'message' => 'Employee created successfully.'.$residenceNote];
    }

    public function updateEmployee(string $companyId, array $payload): array
    {
        $companyId = trim($companyId);
        if ($companyId === '' || !DB::table('employees_master')->where('company_id', $companyId)->exists()) {
            return $this->error('Employee not found.', 404);
        }

        $data = $this->normalizeEmployee($payload);
        unset($data['company_id']);
        $write = $this->employeeWriteData($data, false);
        // Residence fields are owned by the assign/shift flow only.
        // Editing them here would desync employees_master from
        // employee_residence_assignments and electric_v1_occupancy.
        foreach (['unit_id','colony_type','block_floor','room_no','shared_room'] as $residenceField) {
            unset($write[$residenceField]);
        }
        if ($write === ['updated_at' => $write['updated_at'] ?? null] || $write === []) {
            return $this->error('No valid employee fields supplied.', 422);
        }

        DB::transaction(function () use ($companyId, $write) {
            DB::table('employees_master')->where('company_id', $companyId)->update($write);
        });

        return ['status' => 'ok', 'engine' => 'V2', 'company_id' => $companyId, 'message' => 'Employee updated successfully.'];
    }

    public function importEmployees(string $csvText): array
    {
        $parsed = $this->parseEmployeeCsv($csvText);
        if (($parsed['status'] ?? 'error') !== 'ok') {
            return $parsed;
        }

        $inserted = 0;
        $rejected = $parsed['errors'];
        $seen = [];

        DB::transaction(function () use ($parsed, &$inserted, &$rejected, &$seen) {
            foreach ($parsed['rows'] as $item) {
                $rowNo = $item['row_no'];
                $data = $item['data'];
                $companyId = (string) $data['company_id'];

                if (isset($seen[$companyId])) {
                    $rejected[] = ['row_no' => $rowNo, 'company_id' => $companyId, 'error' => 'Duplicate CompanyID in upload'];
                    continue;
                }
                $seen[$companyId] = true;

                if (DB::table('employees_master')->where('company_id', $companyId)->exists()) {
                    $rejected[] = ['row_no' => $rowNo, 'company_id' => $companyId, 'error' => 'CompanyID already exists'];
                    continue;
                }

                try {
                    DB::table('employees_master')->insert($this->employeeWriteData($data, true));
                    $inserted++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $rejected[] = ['row_no' => $rowNo, 'company_id' => $companyId, 'error' => 'CompanyID already exists (race)'];
                }
            }
        });

        return [
            'status' => 'ok', 'engine' => 'V2', 'inserted' => $inserted, 'updated' => 0,
            'rejected' => count($rejected), 'errors_preview' => array_slice($rejected, 0, 100),
        ];
    }

    public function profile(string $companyId): array
    {
        $employee = DB::table('employees_master')->where('company_id', trim($companyId))->first();
        if (!$employee) {
            return $this->error('Employee not found.', 404);
        }

        $cycle = $this->latestCycle();
        $occupancyCycle = $this->occupancySnapshotCycle($cycle);
        $activeResidence = DB::table('employee_residence_assignments')
            ->where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->whereNull('end_date')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        $v2Occupancy = [];
        if ($occupancyCycle !== '') {
            $v2Occupancy = DB::table('util_occupancy_monthly')
                ->where('month_cycle', $occupancyCycle)
                ->where('employee_id', $companyId)
                ->orderBy('unit_id')
                ->orderBy('room_no')
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        $members = DB::table('family_members')
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        $familyRows = [[
            'member_id' => null,
            'member_name' => (string) $employee->name,
            'relation' => 'Family Head / Employee',
            'age' => null,
            'current_status' => strtoupper((string) $employee->active) === 'YES' ? 'ACTIVE' : 'INACTIVE',
            'is_family_head' => true,
        ]];
        foreach ($members as $member) {
            $familyRows[] = [
                'member_id' => (int) $member['id'],
                'member_name' => (string) $member['member_name'],
                'relation' => (string) $member['relation'],
                'age' => $member['age'],
                'school_going' => (bool) $member['school_going'],
                'school_name' => (string) ($member['school_name'] ?? ''),
                'class_name' => (string) ($member['class_name'] ?? ''),
                'current_status' => (string) $member['current_status'],
                'is_family_head' => false,
            ];
        }

        $assets = [];
        foreach (self::ASSET_LABELS as $field => $label) {
            $raw = trim((string) ($employee->{$field} ?? ''));
            if ($raw !== '' && is_numeric($raw) && (float) $raw > 0) {
                $quantity = (float) $raw;
                $assets[] = ['label' => $label, 'quantity' => fmod($quantity, 1.0) === 0.0 ? (int) $quantity : $quantity];
            }
        }

        $residence = [
            'unit_id' => $activeResidence ? (string) $activeResidence->unit_id : (string) ($employee->unit_id ?? ''),
            'residence_type' => $activeResidence ? (string) $activeResidence->residence_type : '',
            'category' => $activeResidence ? (string) ($activeResidence->category ?? '') : '',
            'colony_type' => (string) ($employee->colony_type ?? ''),
            'block_floor' => $activeResidence ? (string) ($activeResidence->block_floor ?? '') : (string) ($employee->block_floor ?? ''),
            'room_no' => $activeResidence ? (string) $activeResidence->room_no : (string) ($employee->room_no ?? ''),
            'occupancy_mode' => $activeResidence ? (string) $activeResidence->occupancy_mode : '',
            'start_date' => $activeResidence ? (string) $activeResidence->start_date : null,
            'status' => $activeResidence ? 'ACTIVE' : ($v2Occupancy !== [] ? 'V2_MONTHLY' : 'UNASSIGNED'),
        ];

        $history = DB::table('employee_residence_assignments')
            ->where('company_id', $companyId)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        return [
            'status' => 'ok',
            'engine' => 'V2',
            'month_cycle' => $cycle,
            'occupancy_month_cycle' => $occupancyCycle,
            'source' => ['employees_master','employee_residence_assignments','family_members','util_occupancy_monthly'],
            'employee' => [
                'company_id' => (string) $employee->company_id,
                'name' => (string) $employee->name,
                'father_name' => (string) ($employee->father_name ?? ''),
                'cnic_no' => (string) ($employee->cnic_no ?? ''),
                'mobile_no' => (string) ($employee->mobile_no ?? ''),
                'department' => (string) ($employee->department ?? ''),
                'section' => (string) ($employee->section ?? ''),
                'sub_section' => (string) ($employee->sub_section ?? ''),
                'designation' => (string) ($employee->designation ?? ''),
                'employee_type' => (string) ($employee->employee_type ?? ''),
                'join_date' => $employee->join_date,
                'leave_date' => $employee->leave_date,
                'active' => strtoupper((string) $employee->active) === 'YES',
                'active_label' => strtoupper((string) $employee->active) === 'YES' ? 'Active' : 'Inactive',
            ],
            'residence' => $residence,
            'v2_occupancy_rows' => $v2Occupancy,
            'family_rows' => $familyRows,
            'residence_history' => $history,
            'assets' => $assets,
            'kpis' => [
                'linked_family_members' => count($members),
                'total_family_members' => 1 + count($members),
                'total_issued_assets' => array_sum(array_map(fn ($asset) => (float) $asset['quantity'], $assets)),
            ],
        ];
    }

    public function families(array $query = []): array
    {
        $q = trim((string) ($query['q'] ?? ''));
        $status = trim((string) ($query['status'] ?? ''));

        $rows = DB::table('family_members as f')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'f.company_id')
            ->when($q !== '', function ($builder) use ($q) {
                $like = "%{$q}%";
                $builder->where(function ($w) use ($like) {
                    $w->where('f.company_id', 'like', $like)
                        ->orWhere('f.member_name', 'like', $like)
                        ->orWhere('f.relation', 'like', $like)
                        ->orWhere('e.name', 'like', $like);
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('f.current_status', $status))
            ->where('f.is_active', 1)
            ->orderBy('f.company_id')
            ->orderBy('f.id')
            ->get([
                'f.id','f.company_id','e.name as employee_name','f.member_name','f.relation','f.age','f.school_going',
                'f.school_name','f.class_name','f.source_residence_type','f.source_colony_building_name','f.source_block_floor',
                'f.source_room_no','f.current_status','f.remarks',
            ])
            ->map(fn ($row) => (array) $row)
            ->all();

        return ['status' => 'ok', 'engine' => 'V2', 'source' => ['family_members','employees_master'], 'count' => count($rows), 'rows' => $rows];
    }

    public function createFamilyMember(string $companyId, array $payload): array
    {
        $companyId = trim($companyId);
        if (!DB::table('employees_master')->where('company_id', $companyId)->exists()) {
            return $this->error('Employee not found.', 404);
        }

        $name = trim((string) ($payload['member_name'] ?? ''));
        $relation = trim((string) ($payload['relation'] ?? ''));
        if ($name === '' || $relation === '') {
            return $this->error('Family member name and relation are required.', 422);
        }

        $age = $this->nullable($payload['age'] ?? null);
        if ($age !== null && (!is_numeric($age) || (float) $age < 0 || (float) $age > 150)) {
            return $this->error('Age must be between 0 and 150.', 422);
        }

        $id = DB::table('family_members')->insertGetId([
            'company_id' => $companyId,
            'member_name' => $name,
            'relation' => $relation,
            'age' => $age === null ? null : round((float) $age, 1),
            'school_going' => filter_var($payload['school_going'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'school_name' => $this->nullable($payload['school_name'] ?? null),
            'class_name' => $this->nullable($payload['class_name'] ?? null),
            'source_month_cycle' => $this->latestCycle() ?: null,
            'source_residence_type' => null,
            'source_colony_building_name' => null,
            'source_block_floor' => null,
            'source_room_no' => null,
            'current_status' => 'PRESENT',
            'is_active' => 1,
            'remarks' => $this->nullable($payload['remarks'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['status' => 'ok', 'engine' => 'V2', 'family_member_id' => (int) $id, 'message' => 'Family member added successfully.'];
    }

    public function occupancy(array $query = []): array
    {
        $billingCycle = $this->latestCycle();
        $requestedCycle = trim((string) ($query['month_cycle'] ?? ''));
        $cycle = $this->occupancySnapshotCycle($requestedCycle !== '' ? $requestedCycle : $billingCycle);
        if ($cycle === '') {
            return $this->error('No V2 occupancy snapshot cycle is available.', 404);
        }

        $rooms = DB::table('util_unit_room_snapshot as r')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'r.unit_id')
            ->where('r.month_cycle', $cycle)
            ->orderBy('r.residence_type')
            ->orderBy('r.unit_id')
            ->orderBy('r.room_no')
            ->get([
                'r.unit_id','r.room_no','r.residence_type','r.category','r.block_floor',
                'u.colony_type','u.is_active as unit_active',
            ]);

        $monthlyRows = DB::table('util_occupancy_monthly as o')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'o.employee_id')
            ->where('o.month_cycle', $cycle)
            ->orderBy('o.unit_id')
            ->orderBy('o.room_no')
            ->orderBy('o.employee_id')
            ->get(['o.unit_id','o.room_no','o.employee_id','o.active_days','e.name as employee_name']);

        $monthlyByRoom = [];
        $employeeRoomCount = [];
        foreach ($monthlyRows as $row) {
            $key = $this->roomKey((string) $row->unit_id, (string) $row->room_no);
            $monthlyByRoom[$key][] = $row;
            $employeeRoomCount[(string) $row->employee_id] = ($employeeRoomCount[(string) $row->employee_id] ?? 0) + 1;
        }

        $currentAssignments = DB::table('employee_residence_assignments as a')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'a.company_id')
            ->where('a.status', 'ACTIVE')
            ->whereNull('a.end_date')
            ->get(['a.unit_id','a.room_no','a.company_id','e.name as employee_name']);
        $currentByRoom = [];
        foreach ($currentAssignments as $row) {
            $currentByRoom[$this->roomKey((string) $row->unit_id, (string) $row->room_no)][] = $row;
        }

        $result = [];
        $known = [];
        foreach ($rooms as $room) {
            $key = $this->roomKey((string) $room->unit_id, (string) $room->room_no);
            $known[$key] = true;
            $occupants = $monthlyByRoom[$key] ?? [];
            $current = $currentByRoom[$key] ?? [];
            $ids = array_values(array_unique(array_map(fn ($r) => (string) $r->employee_id, $occupants)));
            $names = array_values(array_unique(array_filter(array_map(fn ($r) => (string) ($r->employee_name ?? ''), $occupants))));
            $count = count($ids);
            $status = $count === 0 ? 'Vacant' : ($count === 1 ? 'Occupied' : 'Shared');
            $notes = [];

            if ($this->isHouse((string) $room->residence_type) && $count > 1) {
                $status = 'Conflict';
                $notes[] = 'Multiple V2 occupants assigned to a house.';
            }
            foreach ($ids as $employeeId) {
                if (($employeeRoomCount[$employeeId] ?? 0) > 1) {
                    $status = 'Conflict';
                    $notes[] = "Employee {$employeeId} appears in multiple V2 rooms.";
                }
            }
            if ($count === 0 && count($current) > 0) {
                $status = 'Conflict';
                $notes[] = 'Current residence assignment is not present in the V2 monthly occupancy snapshot.';
            }

            $result[] = [
                'month_cycle' => $cycle,
                'residence_type' => (string) ($room->residence_type ?? ''),
                'category' => (string) ($room->category ?? ''),
                'colony_type' => (string) ($room->colony_type ?? ''),
                'block_floor' => (string) ($room->block_floor ?? ''),
                'unit_id' => (string) $room->unit_id,
                'room_no' => (string) $room->room_no,
                'unit_active' => (int) ($room->unit_active ?? 1),
                'occupancy_status' => $status,
                'occupant_count' => $count,
                'assigned_company_ids' => $ids,
                'assigned_employee_names' => $names,
                'active_days' => array_values(array_map(fn ($r) => (int) $r->active_days, $occupants)),
                'current_assignment_count' => count($current),
                'conflict_notes' => array_values(array_unique($notes)),
                'source' => 'V2_MONTHLY_OCCUPANCY',
            ];
        }

        foreach ($monthlyByRoom as $key => $occupants) {
            if (isset($known[$key])) {
                continue;
            }
            [$unitId, $roomNo] = explode('|', $key, 2);
            $ids = array_values(array_unique(array_map(fn ($r) => (string) $r->employee_id, $occupants)));
            $names = array_values(array_unique(array_filter(array_map(fn ($r) => (string) ($r->employee_name ?? ''), $occupants))));
            $result[] = [
                'month_cycle' => $cycle, 'residence_type' => '', 'category' => '', 'colony_type' => '', 'block_floor' => '',
                'unit_id' => $unitId, 'room_no' => $roomNo, 'unit_active' => 1, 'occupancy_status' => 'Conflict',
                'occupant_count' => count($ids), 'assigned_company_ids' => $ids, 'assigned_employee_names' => $names,
                'active_days' => array_values(array_map(fn ($r) => (int) $r->active_days, $occupants)),
                'current_assignment_count' => 0,
                'conflict_notes' => ['V2 occupancy row has no matching room in util_unit_room_snapshot.'],
                'source' => 'V2_MONTHLY_OCCUPANCY',
            ];
        }

        return [
            'status' => 'ok', 'engine' => 'V2', 'month_cycle' => $cycle,
            'billing_month_cycle' => $billingCycle,
            'requested_month_cycle' => $requestedCycle,
            'fallback_used' => ($requestedCycle !== '' ? $requestedCycle : $billingCycle) !== $cycle,
            'source' => ['util_occupancy_monthly','util_unit_room_snapshot','util_unit','employee_residence_assignments'],
            'rows' => $result,
        ];
    }

    public function assignResidence(string $companyId, array $payload): array
    {
        return $this->residenceAction('assign', $companyId, $payload);
    }

    public function shiftResidence(string $companyId, array $payload): array
    {
        return $this->residenceAction('shift', $companyId, $payload);
    }

    public function vacateResidence(string $companyId, array $payload): array
    {
        $companyId = trim($companyId);
        $date = $this->validPastOrTodayDate($payload['effective_date'] ?? null);
        if ($date === null) {
            return $this->error('Valid effective date is required and future dates are not allowed.', 422);
        }

        return DB::transaction(function () use ($companyId, $payload, $date) {
            $active = DB::table('employee_residence_assignments')
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->whereNull('end_date')
                ->lockForUpdate()
                ->first();
            if (!$active) {
                return $this->error('No active residence found to vacate.', 409);
            }
            if ($date <= (string) $active->start_date) {
                return $this->error('Vacate date must be after the current assignment start date.', 422);
            }

            $closedOn = Carbon::createFromFormat('Y-m-d', $date)->subDay()->toDateString();
            DB::table('employee_residence_assignments')->where('id', $active->id)->update([
                'end_date' => $closedOn,
                'status' => 'CLOSED',
                'closure_reason' => strtoupper((string) $active->occupancy_mode) === 'HOUSEHOLD' ? 'FAMILY_SENT_BACK' : 'VACATED',
                'remarks' => $this->nullable($payload['remarks'] ?? null) ?? $active->remarks,
                'updated_at' => now(),
            ]);

            DB::table('employees_master')->where('company_id', $companyId)->update([
                'unit_id' => null, 'colony_type' => null, 'block_floor' => null, 'room_no' => null, 'shared_room' => null,
                'residence_status' => 'UNASSIGNED',
                'updated_at' => now(),
            ]);

            DB::table('electric_v1_occupancy')->where('company_id', $companyId)->delete();

            return [
                'status' => 'ok', 'engine' => 'V2', 'message' => 'Residence vacated successfully.',
                'snapshot_note' => 'The permanent residence was updated. The V2 monthly occupancy snapshot remains unchanged until its controlled monthly refresh.',
            ];
        });
    }

    public function registryGet(string $companyId): array
    {
        $row = DB::table('employees_registry')->where('company_id', trim($companyId))->first();
        if (!$row) {
            return $this->error('Registry employee not found.', 404);
        }
        return ['status' => 'ok', 'engine' => 'V2', 'source' => 'employees_registry', 'row' => $this->toEmployeeRow((array) $row)];
    }

    public function registryUpsert(array $payload): array
    {
        $data = $this->normalizeEmployee($payload);
        $missing = $this->missing($data, ['company_id','name']);
        if ($missing !== []) {
            return $this->error('CompanyID and Name are required.', 422);
        }

        $exists = DB::table('employees_registry')->where('company_id', $data['company_id'])->exists();
        $write = $this->employeeWriteData($data, !$exists, true);
        if ($exists) {
            DB::table('employees_registry')->where('company_id', $data['company_id'])->update($write);
        } else {
            DB::table('employees_registry')->insert($write);
        }

        return ['status' => 'ok', 'engine' => 'V2', 'company_id' => $data['company_id'], 'message' => 'Registry record saved.'];
    }

    public function registryPreview(string $csvText): array
    {
        $parsed = $this->parseEmployeeCsv($csvText);
        if (($parsed['status'] ?? 'error') !== 'ok') {
            return $parsed;
        }

        return [
            'status' => 'ok', 'engine' => 'V2', 'mode' => 'preview',
            'total_rows' => count($parsed['rows']) + count($parsed['errors']),
            'valid_rows' => count($parsed['rows']),
            'failed_rows' => count($parsed['errors']),
            'valid_preview' => array_slice(array_map(fn ($item) => ['row_no' => $item['row_no'], 'row' => $this->toEmployeeRow($item['data'])], $parsed['rows']), 0, 50),
            'errors_preview' => array_slice($parsed['errors'], 0, 100),
        ];
    }

    public function registryCommit(string $csvText): array
    {
        $parsed = $this->parseEmployeeCsv($csvText);
        if (($parsed['status'] ?? 'error') !== 'ok') {
            return $parsed;
        }

        $inserted = 0;
        $updated = 0;
        $errors = $parsed['errors'];
        $seen = [];

        DB::transaction(function () use ($parsed, &$inserted, &$updated, &$errors, &$seen) {
            foreach ($parsed['rows'] as $item) {
                $data = $item['data'];
                $companyId = (string) $data['company_id'];
                if (isset($seen[$companyId])) {
                    $errors[] = ['row_no' => $item['row_no'], 'company_id' => $companyId, 'error' => 'Duplicate CompanyID in upload'];
                    continue;
                }
                $seen[$companyId] = true;
                $exists = DB::table('employees_registry')->where('company_id', $companyId)->exists();
                $write = $this->employeeWriteData($data, !$exists, true);
                if ($exists) {
                    DB::table('employees_registry')->where('company_id', $companyId)->update($write);
                    $updated++;
                } else {
                    DB::table('employees_registry')->insert($write);
                    $inserted++;
                }
            }
        });

        return [
            'status' => 'ok', 'engine' => 'V2', 'mode' => 'commit', 'inserted' => $inserted, 'updated' => $updated,
            'rejected' => count($errors), 'errors_preview' => array_slice($errors, 0, 100),
        ];
    }

    public function residenceTypes(): array
    {
        $cycle = $this->roomSnapshotCycle($this->latestCycle());
        $rows = DB::table('util_unit_room_snapshot')
            ->where('month_cycle', $cycle)
            ->whereNotNull('residence_type')->where('residence_type', '<>', '')
            ->distinct()->orderBy('residence_type')->pluck('residence_type')->values()->all();
        return ['status' => 'ok', 'engine' => 'V2', 'month_cycle' => $cycle, 'rows' => $rows];
    }

    public function colonies(string $residenceType = ''): array
    {
        $cycle = $this->roomSnapshotCycle($this->latestCycle());
        $query = DB::table('util_unit_room_snapshot as r')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'r.unit_id')
            ->where('r.month_cycle', $cycle);
        if ($residenceType !== '') {
            $query->where('r.residence_type', $residenceType);
        }
        $rows = $query->selectRaw("COALESCE(NULLIF(u.colony_type,''), '__uncategorized') as colony_type")
            ->distinct()->orderBy('colony_type')->pluck('colony_type')->values()->all();
        return ['status' => 'ok', 'engine' => 'V2', 'month_cycle' => $cycle, 'rows' => $rows];
    }

    public function blocks(string $colony, string $residenceType = ''): array
    {
        $cycle = $this->roomSnapshotCycle($this->latestCycle());
        $query = DB::table('util_unit_room_snapshot as r')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'r.unit_id')
            ->where('r.month_cycle', $cycle)
            ->whereNotNull('r.block_floor')->where('r.block_floor', '<>', '');
        if ($residenceType !== '') {
            $query->where('r.residence_type', $residenceType);
        }
        $this->applyColony($query, $colony);
        $rows = $query->distinct()->orderBy('r.block_floor')->pluck('r.block_floor')->values()->all();
        return ['status' => 'ok', 'engine' => 'V2', 'month_cycle' => $cycle, 'rows' => $rows];
    }

    public function rooms(string $colony, string $block, string $residenceType = ''): array
    {
        $cycle = $this->roomSnapshotCycle($this->latestCycle());
        $query = DB::table('util_unit_room_snapshot as r')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'r.unit_id')
            ->where('r.month_cycle', $cycle)
            ->where('r.block_floor', $block)
            ->whereNotNull('r.room_no')->where('r.room_no', '<>', '');
        if ($residenceType !== '') {
            $query->where('r.residence_type', $residenceType);
        }
        $this->applyColony($query, $colony);
        $rows = $query->orderBy('r.room_no')->get(['r.room_no','r.unit_id'])->map(fn ($r) => (array) $r)->all();
        return ['status' => 'ok', 'engine' => 'V2', 'month_cycle' => $cycle, 'rows' => $rows];
    }

    private function residenceAction(string $mode, string $companyId, array $payload): array
    {
        $companyId = trim($companyId);
        $unitId = trim((string) ($payload['unit_id'] ?? ''));
        $roomNo = trim((string) ($payload['room_no'] ?? ''));
        $date = $this->validPastOrTodayDate($payload['effective_date'] ?? null);
        if ($unitId === '' || $roomNo === '' || $date === null) {
            return $this->error('Unit, room and a valid effective date are required. Future dates are not allowed.', 422);
        }

        return DB::transaction(function () use ($mode, $companyId, $unitId, $roomNo, $date, $payload) {
            $employee = DB::table('employees_master')->where('company_id', $companyId)->lockForUpdate()->first();
            if (!$employee) {
                return $this->error('Employee not found.', 404);
            }

            $active = DB::table('employee_residence_assignments')
                ->where('company_id', $companyId)->where('status', 'ACTIVE')->whereNull('end_date')
                ->lockForUpdate()->first();
            if ($mode === 'assign' && $active) {
                return $this->error('Employee already has an active residence. Use transfer.', 409);
            }
            if ($mode === 'shift' && !$active) {
                return $this->error('No active residence found. Use assign.', 409);
            }
            if ($active && $date <= (string) $active->start_date) {
                return $this->error('Effective date must be after the current assignment start date.', 422);
            }
            if ($active && (string) $active->unit_id === $unitId && (string) $active->room_no === $roomNo) {
                return $this->error('New residence must be different from the current residence.', 409);
            }

            // OUTSIDE: no physical room. Close assignment, mark OUTSIDE, stop billing.
            if ($unitId === 'Outside Colony' || $unitId === 'OUTSIDE') {
                if ($active) {
                    DB::table('employee_residence_assignments')->where('id', $active->id)->update([
                        'end_date' => Carbon::createFromFormat('Y-m-d', $date)->subDay()->toDateString(),
                        'status' => 'CLOSED', 'closure_reason' => 'MOVED_OUTSIDE', 'updated_at' => now(),
                    ]);
                }
                DB::table('employees_master')->where('company_id', $companyId)->update([
                    'residence_status' => 'OUTSIDE',
                    'unit_id' => 'OUTSIDE',
                    'colony_type' => 'OUTSIDE',
                    'room_no' => null,
                    'block_floor' => null,
                    'shared_room' => 'No',
                    'updated_at' => now(),
                ]);
                DB::table('electric_v1_occupancy')->where('company_id', $companyId)->delete();
                return ['status' => 'ok', 'engine' => 'V2', 'message' => 'Employee marked as Outside Colony. Billing stopped.'];
            }

            $room = $this->findRoomForUpdate($unitId, $roomNo);
            if (!$room) {
                return $this->error('Selected V2 residence room was not found.', 404);
            }
            if (!$this->isEligibleResidence((string) ($room->residence_type ?? ''))) {
                return $this->error('Selected room is not an eligible employee residence.', 422);
            }
            if ($this->isHouse((string) $room->residence_type)) {
                $query = DB::table('employee_residence_assignments')
                    ->where('status', 'ACTIVE')->whereNull('end_date')
                    ->where('unit_id', $unitId)->where('room_no', $roomNo);
                if ($active) {
                    $query->where('id', '<>', $active->id);
                }
                if ($query->exists()) {
                    return $this->error('Selected house is already occupied.', 409);
                }
            }

            if ($active) {
                DB::table('employee_residence_assignments')->where('id', $active->id)->update([
                    'end_date' => Carbon::createFromFormat('Y-m-d', $date)->subDay()->toDateString(),
                    'status' => 'CLOSED', 'closure_reason' => 'SHIFTED', 'updated_at' => now(),
                ]);
            }

            $modeValue = $this->isHouse((string) $room->residence_type)
                && DB::table('family_members')->where('company_id', $companyId)->where('is_active', 1)->exists()
                ? 'HOUSEHOLD' : 'INDIVIDUAL';

            $assignmentId = DB::table('employee_residence_assignments')->insertGetId([
                'company_id' => $companyId,
                'residence_type' => (string) $room->residence_type,
                'category' => $room->category ?? null,
                'unit_id' => $unitId,
                'block_floor' => $room->block_floor ?? null,
                'room_no' => $roomNo,
                'occupancy_mode' => $modeValue,
                'start_date' => $date,
                'end_date' => null,
                'status' => 'ACTIVE',
                'start_reason' => $active ? 'SHIFT' : 'ASSIGN',
                'closure_reason' => null,
                'source_month_cycle' => $this->latestCycle() ?: null,
                'source_record_type' => 'V2_PEOPLE_RESIDENCY',
                'remarks' => $this->nullable($payload['remarks'] ?? null),
                'created_by' => $this->nullable($payload['created_by'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $colony = DB::table('util_unit')->where('unit_id', $unitId)->value('colony_type');
            $shared = !$this->isHouse((string) $room->residence_type)
                && DB::table('employee_residence_assignments')->where('status', 'ACTIVE')->whereNull('end_date')
                    ->where('unit_id', $unitId)->where('room_no', $roomNo)->count() > 1;

            DB::table('employees_master')->where('company_id', $companyId)->update([
                'unit_id' => $unitId,
                'colony_type' => $colony,
                'block_floor' => $room->block_floor ?? null,
                'room_no' => $roomNo,
                'shared_room' => $shared ? 'Yes' : 'No',
                'residence_status' => 'RESIDENT',
                'updated_at' => now(),
            ]);

            $cyc = DB::table('util_month_cycle')->orderByDesc('cycle_start_date')->first();
            DB::table('electric_v1_occupancy')->where('company_id', $companyId)->delete();
            DB::table('electric_v1_occupancy')->insert([
                'company_id' => $companyId,
                'unit_id'    => $unitId,
                'room_id'    => $roomNo,
                'from_date'  => $cyc->cycle_start_date ?? $date,
                'to_date'    => $cyc->cycle_end_date ?? $date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => 'ok', 'engine' => 'V2', 'assignment_id' => (int) $assignmentId,
                'message' => $active ? 'Residence transferred successfully.' : 'Residence assigned successfully.',
                'snapshot_note' => 'The permanent residence was updated. The V2 monthly occupancy snapshot remains unchanged until its controlled monthly refresh.',
            ];
        });
    }


    private function hrSnapshotCycle(string $preferredCycle = ''): string
    {
        if (!Schema::hasTable('hr_active_employee_snapshots')) {
            return '';
        }

        $iso = $this->toIsoCycle($preferredCycle);
        if ($iso !== '' && DB::table('hr_active_employee_snapshots')->where('month_cycle', $iso)->exists()) {
            return $iso;
        }

        return $this->latestTableCycle('hr_active_employee_snapshots');
    }

    private function roomSnapshotCycle(string $preferredCycle = ''): string
    {
        if (!Schema::hasTable('util_unit_room_snapshot')) {
            return '';
        }

        $preferredCycle = trim($preferredCycle);
        if ($preferredCycle !== '' && DB::table('util_unit_room_snapshot')->where('month_cycle', $preferredCycle)->exists()) {
            return $preferredCycle;
        }

        return $this->latestTableCycle('util_unit_room_snapshot');
    }

    private function occupancySnapshotCycle(string $preferredCycle = ''): string
    {
        if (!Schema::hasTable('util_occupancy_monthly') || !Schema::hasTable('util_unit_room_snapshot')) {
            return '';
        }

        $preferredCycle = trim($preferredCycle);
        if ($preferredCycle !== ''
            && DB::table('util_occupancy_monthly')->where('month_cycle', $preferredCycle)->exists()
            && DB::table('util_unit_room_snapshot')->where('month_cycle', $preferredCycle)->exists()) {
            return $preferredCycle;
        }

        $occupancyCycles = DB::table('util_occupancy_monthly')->whereNotNull('month_cycle')->distinct()->pluck('month_cycle')
            ->map(fn ($cycle) => trim((string) $cycle))->filter()->all();
        $roomCycles = DB::table('util_unit_room_snapshot')->whereNotNull('month_cycle')->distinct()->pluck('month_cycle')
            ->map(fn ($cycle) => trim((string) $cycle))->filter()->all();
        $common = array_values(array_intersect($occupancyCycles, $roomCycles));
        usort($common, fn ($a, $b) => $this->cycleTimestamp((string) $b) <=> $this->cycleTimestamp((string) $a));

        return (string) ($common[0] ?? '');
    }

    private function latestTableCycle(string $table): string
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'month_cycle')) {
            return '';
        }

        $cycles = DB::table($table)->whereNotNull('month_cycle')->distinct()->pluck('month_cycle')
            ->map(fn ($cycle) => trim((string) $cycle))->filter()->values()->all();
        usort($cycles, fn ($a, $b) => $this->cycleTimestamp((string) $b) <=> $this->cycleTimestamp((string) $a));

        return (string) ($cycles[0] ?? '');
    }

    private function toIsoCycle(string $cycle): string
    {
        $cycle = trim($cycle);
        if (preg_match('/^(\d{2})-(\d{4})$/', $cycle, $match)) {
            return $match[2].'-'.$match[1];
        }
        if (preg_match('/^(\d{4})-(\d{2})$/', $cycle)) {
            return $cycle;
        }
        return '';
    }

    private function cycleTimestamp(string $cycle): int
    {
        $cycle = trim($cycle);
        foreach (['m-Y', 'Y-m'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $cycle);
                if ($date && $date->format($format) === $cycle) {
                    return $date->timestamp;
                }
            } catch (Throwable) {
            }
        }
        return 0;
    }

    private function latestCycle(): string
    {
        $cycle = '';
        if (Schema::hasTable('util_month_cycle')) {
            $cycle = (string) (DB::table('util_month_cycle')
                ->where('state', 'OPEN')
                ->orderByDesc('cycle_start_date')
                ->value('month_cycle') ?? '');
        }
        if ($cycle === '' && Schema::hasTable('util_occupancy_monthly')) {
            $cycle = (string) (DB::table('util_occupancy_monthly')->max('month_cycle') ?? '');
        }
        if ($cycle === '' && Schema::hasTable('util_unit_room_snapshot')) {
            $cycle = (string) (DB::table('util_unit_room_snapshot')->max('month_cycle') ?? '');
        }
        return trim($cycle);
    }

    private function findRoomForUpdate(string $unitId, string $roomNo): ?object
    {
        return DB::table('util_unit_rooms')
            ->select('unit_id','room_no','residence_type','floor as block_floor','residence_type as category')
            ->where('is_active', 1)
            ->where('unit_id', $unitId)
            ->where('room_no', $roomNo)
            ->lockForUpdate()
            ->first();
    }

    private function isEligibleResidence(string $type): bool
    {
        $type = strtoupper(trim($type));
        return str_starts_with($type, 'HOUSE') || in_array($type, ['ROOM','CONTAINER'], true);
    }

    private function isHouse(string $type): bool
    {
        $t = strtoupper(trim($type));
        // HOUSE_A+ (executive banglow) is shared, not single-occupancy
        return $t !== 'HOUSE_A+' && str_starts_with($t, 'HOUSE');
    }

    private function validPastOrTodayDate(mixed $raw): ?string
    {
        $value = trim((string) $raw);
        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
        } catch (Throwable) {
            return null;
        }
        if ($date->format("Y-m-d") !== $value) {
            return null;
        }
        return $value;
    }

    private function normalizeEmployee(array $payload): array
    {
        $out = [];
        foreach ($payload as $key => $value) {
            if (isset(self::EMPLOYEE_MAP[$key])) {
                $out[self::EMPLOYEE_MAP[$key]] = is_string($value) ? trim($value) : $value;
            }
        }
        if (array_key_exists('active', $out)) {
            $active = strtolower(trim((string) $out['active']));
            $out['active'] = in_array($active, ['yes','1','true','active'], true) ? 'Yes' : (in_array($active, ['no','0','false','inactive'], true) ? 'No' : trim((string) $out['active']));
        }
        return $out;
    }

    private function employeeWriteData(array $data, bool $creating, bool $registry = false): array
    {
        $write = [];
        $allowed = self::EMPLOYEE_FIELDS;
        if ($registry) {
            $allowed = array_values(array_filter($allowed, fn ($field) => $field !== 'remarks' || Schema::hasColumn('employees_registry', 'remarks')));
        }
        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $write[$field] = $this->nullable($data[$field]);
        }
        if ($creating) {
            $write['company_id'] = trim((string) ($data['company_id'] ?? ''));
            $write['name'] = trim((string) ($data['name'] ?? ''));
            $write['active'] = trim((string) ($data['active'] ?? 'Yes')) ?: 'Yes';
            $write['created_at'] = now();
        } else {
            unset($write['company_id']);
        }
        $write['updated_at'] = now();
        return $write;
    }

    private function toEmployeeRow(array $row): array
    {
        $api = [
            'company_id' => $row['company_id'] ?? null, 'name' => $row['name'] ?? null,
            'department' => $row['department'] ?? null, 'designation' => $row['designation'] ?? null,
            'unit_id' => $row['unit_id'] ?? null, 'active' => $row['active'] ?? null,
            'CompanyID' => $row['company_id'] ?? null, 'Name' => $row['name'] ?? null,
            "Father's Name" => $row['father_name'] ?? null, 'CNIC_No.' => $row['cnic_no'] ?? null,
            'Mobile_No.' => $row['mobile_no'] ?? null, 'Department' => $row['department'] ?? null,
            'Section' => $row['section'] ?? null, 'Sub Section' => $row['sub_section'] ?? null,
            'Designation' => $row['designation'] ?? null, 'Employee Type' => $row['employee_type'] ?? null,
            'Join Date' => $row['join_date'] ?? null, 'Leave Date' => $row['leave_date'] ?? null,
            'Unit_ID' => $row['unit_id'] ?? null, 'Colony Type' => $row['colony_type'] ?? null,
            'Block Floor' => $row['block_floor'] ?? null, 'Room No' => $row['room_no'] ?? null,
            'Shared Room' => $row['shared_room'] ?? null, 'Active' => $row['active'] ?? null,
            'Remarks' => $row['remarks'] ?? null,
        ];
        foreach (self::ASSET_LABELS as $field => $label) {
            $api[$label === 'Wifi Router' ? 'Wifi Rtr' : ($label === 'LPG Cylinder' ? 'LPG cylinder' : $label)] = $row[$field] ?? null;
        }
        return $api;
    }

    private function parseEmployeeCsv(string $csvText): array
    {
        $csvText = preg_replace('/^\\xEF\\xBB\\xBF/', '', $csvText);
        $csvText = trim($csvText);
        if ($csvText === '') {
            return $this->error('csv_text is required.', 422);
        }
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $csvText);
        rewind($handle);
        $headers = fgetcsv($handle);
        if (!is_array($headers) || $headers === []) {
            fclose($handle);
            return $this->error('CSV header is missing.', 422);
        }
        $headers = array_map(fn ($value) => trim((string) $value), $headers);
        if (!in_array('CompanyID', $headers, true) || !in_array('Name', $headers, true)) {
            fclose($handle);
            return $this->error('CSV must contain CompanyID and Name columns.', 422);
        }

        $rows = [];
        $errors = [];
        $lineNo = 1;
        while (($line = fgetcsv($handle)) !== false) {
            $lineNo++;
            if ($line === [] || $line === [null]) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $index => $header) {
                $assoc[$header] = trim((string) ($line[$index] ?? ''));
            }
            $data = $this->normalizeEmployee($assoc);
            $missing = $this->missing($data, ['company_id','name']);
            if ($missing !== []) {
                $errors[] = ['row_no' => $lineNo, 'error' => 'Missing required: '.implode(', ', $missing)];
                continue;
            }
            if (($data['active'] ?? '') === '') {
                $data['active'] = 'Yes';
            }
            $rows[] = ['row_no' => $lineNo, 'data' => $data];
        }
        fclose($handle);
        return ['status' => 'ok', 'rows' => $rows, 'errors' => $errors];
    }

    private function missing(array $data, array $fields): array
    {
        return array_values(array_filter($fields, fn ($field) => trim((string) ($data[$field] ?? '')) === ''));
    }

    private function nullable(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function roomKey(string $unitId, string $roomNo): string
    {
        return $unitId.'|'.$roomNo;
    }

    private function applyColony($query, string $colony): void
    {
        if ($colony === '__uncategorized') {
            $query->where(function ($w) {
                $w->whereNull('u.colony_type')->orWhere('u.colony_type', '');
            });
        } else {
            $query->where('u.colony_type', $colony);
        }
    }

    private function error(string $message, int $http, array $extra = []): array
    {
        return array_merge(['status' => 'error', 'engine' => 'V2', 'error' => $message, '_http' => $http], $extra);
    }
}
