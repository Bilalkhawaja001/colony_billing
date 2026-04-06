<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class EmployeesMeterParityService
{
    private const COLUMN_MAP = [
        'CompanyID' => 'company_id',
        'company_id' => 'company_id',
        'Name' => 'name',
        'name' => 'name',
        "Father's Name" => 'father_name',
        'father_name' => 'father_name',
        'CNIC_No.' => 'cnic_no',
        'cnic_no' => 'cnic_no',
        'Mobile_No.' => 'mobile_no',
        'mobile_no' => 'mobile_no',
        'Department' => 'department',
        'department' => 'department',
        'Section' => 'section',
        'section' => 'section',
        'Sub Section' => 'sub_section',
        'sub_section' => 'sub_section',
        'Designation' => 'designation',
        'designation' => 'designation',
        'Employee Type' => 'employee_type',
        'employee_type' => 'employee_type',
        'Colony Type' => 'colony_type',
        'colony_type' => 'colony_type',
        'Block Floor' => 'block_floor',
        'block_floor' => 'block_floor',
        'Room No' => 'room_no',
        'room_no' => 'room_no',
        'Shared Room' => 'shared_room',
        'shared_room' => 'shared_room',
        'Join Date' => 'join_date',
        'join_date' => 'join_date',
        'Leave Date' => 'leave_date',
        'leave_date' => 'leave_date',
        'Active' => 'active',
        'active' => 'active',
        'Remarks' => 'remarks',
        'remarks' => 'remarks',
        'Unit_ID' => 'unit_id',
        'unit_id' => 'unit_id',
        'Iron Cot' => 'iron_cot',
        'Single Bed' => 'single_bed',
        'Double Bed' => 'double_bed',
        'Mattress' => 'mattress',
        'Sofa Set' => 'sofa_set',
        'Bed Sheet' => 'bed_sheet',
        'Wardrobe' => 'wardrobe',
        'Centre Table' => 'centre_table',
        'Wooden Chair' => 'wooden_chair',
        'Dinning Table' => 'dinning_table',
        'Dinning Chair' => 'dinning_chair',
        'Side Table' => 'side_table',
        'Fridge' => 'fridge',
        'Water Dispenser' => 'water_dispenser',
        'Washing Machine' => 'washing_machine',
        'Air Cooler' => 'air_cooler',
        'A/C' => 'ac',
        'LED' => 'led',
        'Gyser' => 'gyser',
        'Electric Kettle' => 'electric_kettle',
        'Wifi Rtr' => 'wifi_rtr',
        'Water Bottle' => 'water_bottle',
        'LPG cylinder' => 'lpg_cylinder',
        'Gas Stove' => 'gas_stove',
        'Crockery' => 'crockery',
        'Kitchen Cabinet' => 'kitchen_cabinet',
        'Mug' => 'mug',
        'Bucket' => 'bucket',
        'Mirror' => 'mirror',
        'Dustbin' => 'dustbin',
    ];

    private const CSV_REQUIRED_CANONICAL = ['CompanyID', 'Name', 'CNIC_No.', 'Department', 'Designation', 'Unit_ID'];

    public function employees(array $query): array
    {
        $q = trim((string) ($query['q'] ?? ''));
        $department = trim((string) ($query['department'] ?? ''));
        $active = trim((string) ($query['active'] ?? ''));
        $activeOnly = ((string) ($query['active_only'] ?? '') === '1');

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
            ->when($department !== '', fn ($b) => $b->where('department', $department))
            ->when($active !== '', fn ($b) => $b->where('active', $active))
            ->when($activeOnly, fn ($b) => $b->where('active', 'Yes'))
            ->orderBy('company_id')
            ->get()
            ->map(fn ($r) => $this->toApiRow((array) $r))
            ->all();

        return ['status' => 'ok', 'rows' => $rows];
    }

    public function employeesSearch(array $query): array
    {
        $q = trim((string) ($query['q'] ?? ''));
        if ($q === '') {
            return ['status' => 'error', 'error' => 'q required', '_http' => 400];
        }

        $rows = DB::table('employees_master')
            ->where(function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where('company_id', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('cnic_no', 'like', $like)
                    ->orWhere('department', 'like', $like)
                    ->orWhere('designation', 'like', $like);
            })
            ->orderBy('company_id')
            ->limit(100)
            ->get()
            ->map(fn ($r) => $this->toApiRow((array) $r))
            ->all();

        return ['status' => 'ok', 'rows' => $rows];
    }

    public function employeeGet(string $companyId): array
    {
        $row = DB::table('employees_master')->where('company_id', $companyId)->first();
        if (!$row) {
            return ['status' => 'error', 'error' => 'CompanyID not found', '_http' => 404];
        }

        return ['status' => 'ok', 'row' => $this->toApiRow((array) $row)];
    }

    public function employeesDepartments(): array
    {
        $rows = DB::table('employees_master')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->values();

        return ['status' => 'ok', 'rows' => $rows];
    }

    public function employeesImport(array $payload): array
    {
        $csvText = trim((string) ($payload['csv_text'] ?? ''));
        if ($csvText === '') {
            return ['status' => 'error', 'error' => 'csv_text required', '_http' => 400];
        }

        $rows = $this->csvToAssoc($csvText);
        if ($rows === []) {
            return ['status' => 'error', 'error' => 'csv_text must include header and at least one row', '_http' => 400];
        }

        $normalizedRows = [];
        $errors = [];

        foreach ($rows as $idx => $row) {
            $line = $idx + 2;
            $n = $this->normalizePayload($row);

            // Backward compatibility: reduced CSV can still import.
            $required = ['company_id', 'name'];
            $missing = array_values(array_filter($required, fn ($f) => ($n[$f] ?? '') === ''));
            if ($missing !== []) {
                $errors[] = ['row_no' => $line, 'error' => 'Missing required: '.implode(', ', $missing)];
                continue;
            }

            if (($n['active'] ?? '') === '') {
                $n['active'] = 'Yes';
            }

            $normalizedRows[] = ['row_no' => $line, 'row' => $n];
        }

        $inserted = 0;
        $updated = 0;
        DB::transaction(function () use ($normalizedRows, &$inserted, &$updated) {
            foreach ($normalizedRows as $item) {
                $row = $item['row'];
                $exists = DB::table('employees_master')->where('company_id', $row['company_id'])->exists();

                DB::table('employees_master')->updateOrInsert(
                    ['company_id' => $row['company_id']],
                    $this->buildUpsertData($row)
                );

                if ($exists) {
                    $updated++;
                } else {
                    $inserted++;
                }
            }
        });

        return [
            'status' => 'ok',
            'inserted' => $inserted,
            'updated' => $updated,
            'rejected' => count($errors),
            'errors_preview' => array_slice($errors, 0, 50),
        ];
    }

    public function employeesUpsert(array $payload): array
    {
        $data = $this->normalizePayload($payload);

        $companyId = trim((string) ($data['company_id'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        if ($companyId === '' || $name === '') {
            return ['status' => 'error', 'error' => 'company_id and name are required', '_http' => 400];
        }

        DB::table('employees_master')->updateOrInsert(
            ['company_id' => $companyId],
            $this->buildUpsertData($data)
        );

        return ['status' => 'ok', 'company_id' => $companyId, 'CompanyID' => $companyId];
    }

    public function employeesAdd(array $payload): array
    {
        $data = $this->normalizePayload($payload);

        // Flask canonical required for Add Employee flow.
        $missing = [];
        foreach (['company_id', 'name', 'cnic_no', 'department', 'designation', 'unit_id'] as $f) {
            if (($data[$f] ?? '') === '') {
                $missing[] = $f;
            }
        }
        if ($missing !== []) {
            return ['status' => 'error', 'error' => 'Missing mandatory fields', 'missing_fields' => $missing, '_http' => 400];
        }

        if (DB::table('employees_master')->where('company_id', $data['company_id'])->exists()) {
            return ['status' => 'error', 'error' => 'CompanyID already exists', '_http' => 409];
        }

        DB::table('employees_master')->insert($this->buildUpsertData($data));

        return ['status' => 'ok', 'company_id' => $data['company_id'], 'CompanyID' => $data['company_id']];
    }

    public function employeePatch(string $companyId, array $payload): array
    {
        if (!DB::table('employees_master')->where('company_id', $companyId)->exists()) {
            return ['status' => 'error', 'error' => 'CompanyID not found', '_http' => 404];
        }

        $data = $this->normalizePayload($payload);
        $allowed = [
            'name','father_name','mobile_no','department','section','sub_section','designation','employee_type','join_date','leave_date',
            'active','colony_type','block_floor','room_no','shared_room','unit_id','remarks',
            'iron_cot','single_bed','double_bed','mattress','sofa_set','bed_sheet','wardrobe','centre_table','wooden_chair','dinning_table',
            'dinning_chair','side_table','fridge','water_dispenser','washing_machine','air_cooler','ac','led','gyser','electric_kettle','wifi_rtr',
            'water_bottle','lpg_cylinder','gas_stove','crockery','kitchen_cabinet','mug','bucket','mirror','dustbin'
        ];

        $updates = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updates[$field] = $data[$field] === '' ? null : $data[$field];
            }
        }

        if ($updates === []) {
            return ['status' => 'error', 'error' => 'No valid patch fields provided', '_http' => 400];
        }

        $updates['updated_at'] = now();
        DB::table('employees_master')->where('company_id', $companyId)->update($updates);

        return ['status' => 'ok', 'company_id' => $companyId, 'CompanyID' => $companyId];
    }

    public function employeeDelete(string $companyId): array
    {
        $affected = DB::table('employees_master')->where('company_id', $companyId)->update([
            'active' => 'No',
            'updated_at' => now(),
        ]);

        if ($affected === 0) {
            return ['status' => 'error', 'error' => 'CompanyID not found', '_http' => 404];
        }

        return ['status' => 'ok', 'company_id' => $companyId, 'CompanyID' => $companyId, 'policy' => 'soft-delete'];
    }

    public function meterReadingLatest(string $unitId): array
    {
        $row = DB::table('util_meter_readings')
            ->where('unit_id', $unitId)
            ->orderByDesc('reading_date')
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            return ['status' => 'error', 'error' => 'No meter reading found', '_http' => 404];
        }

        return ['status' => 'ok', 'row' => $row];
    }

    public function meterReadingUpsert(array $payload): array
    {
        $meterId = trim((string) ($payload['meter_id'] ?? ''));
        $unitId = trim((string) ($payload['unit_id'] ?? ''));
        $readingDate = trim((string) ($payload['reading_date'] ?? date('Y-m-d')));
        $readingValue = $payload['reading_value'] ?? null;

        if ($meterId === '' || $unitId === '' || $readingValue === null || $readingValue === '') {
            return ['status' => 'error', 'error' => 'meter_id, unit_id, reading_value are required', '_http' => 400];
        }

        DB::table('util_meter_readings')->updateOrInsert(
            ['meter_id' => $meterId, 'reading_date' => $readingDate],
            [
                'unit_id' => $unitId,
                'reading_value' => (float) $readingValue,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return ['status' => 'ok', 'meter_id' => $meterId, 'reading_date' => $readingDate];
    }

    public function meterUnit(array $query): array
    {
        $q = trim((string) ($query['q'] ?? ''));

        $rows = DB::table('util_meter_unit')
            ->when($q !== '', function ($b) use ($q) {
                $like = "%{$q}%";
                $b->where(function ($w) use ($like) {
                    $w->where('meter_id', 'like', $like)
                        ->orWhere('unit_id', 'like', $like)
                        ->orWhere('meter_type', 'like', $like);
                });
            })
            ->orderBy('meter_id')
            ->get();

        return ['status' => 'ok', 'rows' => $rows];
    }

    public function meterUnitUpsert(array $payload): array
    {
        $meterId = trim((string) ($payload['meter_id'] ?? ''));
        $unitId = trim((string) ($payload['unit_id'] ?? ''));
        if ($meterId === '' || $unitId === '') {
            return ['status' => 'error', 'error' => 'meter_id and unit_id are required', '_http' => 400];
        }

        DB::table('util_meter_unit')->updateOrInsert(
            ['meter_id' => $meterId],
            [
                'unit_id' => $unitId,
                'meter_type' => $this->nullable($payload['meter_type'] ?? null),
                'is_active' => (int) ($payload['is_active'] ?? 1),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return ['status' => 'ok', 'meter_id' => $meterId];
    }

    public function roomsCascade(array $payload): array
    {
        $monthCycle = trim((string) ($payload['month_cycle'] ?? ''));
        $unitId = trim((string) ($payload['unit_id'] ?? ''));

        if ($monthCycle === '' || $unitId === '') {
            return ['status' => 'error', 'error' => 'month_cycle and unit_id are required', '_http' => 400];
        }

        $deletedOccupancy = DB::table('util_occupancy_monthly')
            ->where('month_cycle', $monthCycle)
            ->where('unit_id', $unitId)
            ->delete();

        $deletedRooms = DB::table('util_unit_room_snapshot')
            ->where('month_cycle', $monthCycle)
            ->where('unit_id', $unitId)
            ->delete();

        return [
            'status' => 'ok',
            'month_cycle' => $monthCycle,
            'unit_id' => $unitId,
            'deleted' => [
                'occupancy_rows' => $deletedOccupancy,
                'rooms_rows' => $deletedRooms,
            ],
        ];
    }

    private function normalizePayload(array $payload): array
    {
        $out = [];
        foreach ($payload as $k => $v) {
            if (isset(self::COLUMN_MAP[$k])) {
                $col = self::COLUMN_MAP[$k];
                $out[$col] = is_string($v) ? trim($v) : $v;
            }
        }

        return $out;
    }

    private function buildUpsertData(array $data): array
    {
        $fields = [
            'company_id','name','father_name','cnic_no','mobile_no','department','section','sub_section','designation','employee_type',
            'colony_type','block_floor','room_no','shared_room','join_date','leave_date','active','remarks','unit_id',
            'iron_cot','single_bed','double_bed','mattress','sofa_set','bed_sheet','wardrobe','centre_table','wooden_chair',
            'dinning_table','dinning_chair','side_table','fridge','water_dispenser','washing_machine','air_cooler','ac','led',
            'gyser','electric_kettle','wifi_rtr','water_bottle','lpg_cylinder','gas_stove','crockery','kitchen_cabinet','mug',
            'bucket','mirror','dustbin'
        ];

        $row = [];
        foreach ($fields as $f) {
            if ($f === 'active') {
                $row[$f] = trim((string) ($data[$f] ?? 'Yes')) ?: 'Yes';
                continue;
            }
            if ($f === 'company_id' || $f === 'name') {
                $row[$f] = trim((string) ($data[$f] ?? ''));
                continue;
            }
            $row[$f] = $this->nullable($data[$f] ?? null);
        }

        $row['created_at'] = now();
        $row['updated_at'] = now();

        return $row;
    }

    private function toApiRow(array $row): array
    {
        return [
            // legacy keys
            'company_id' => $row['company_id'] ?? null,
            'name' => $row['name'] ?? null,
            'department' => $row['department'] ?? null,
            'designation' => $row['designation'] ?? null,
            'unit_id' => $row['unit_id'] ?? null,
            'active' => $row['active'] ?? null,

            // canonical keys
            'CompanyID' => $row['company_id'] ?? null,
            'Name' => $row['name'] ?? null,
            "Father's Name" => $row['father_name'] ?? null,
            'CNIC_No.' => $row['cnic_no'] ?? null,
            'Mobile_No.' => $row['mobile_no'] ?? null,
            'Department' => $row['department'] ?? null,
            'Section' => $row['section'] ?? null,
            'Sub Section' => $row['sub_section'] ?? null,
            'Designation' => $row['designation'] ?? null,
            'Employee Type' => $row['employee_type'] ?? null,
            'Colony Type' => $row['colony_type'] ?? null,
            'Block Floor' => $row['block_floor'] ?? null,
            'Room No' => $row['room_no'] ?? null,
            'Shared Room' => $row['shared_room'] ?? null,
            'Join Date' => $row['join_date'] ?? null,
            'Leave Date' => $row['leave_date'] ?? null,
            'Active' => $row['active'] ?? null,
            'Remarks' => $row['remarks'] ?? null,
            'Unit_ID' => $row['unit_id'] ?? null,
            'Iron Cot' => $row['iron_cot'] ?? null,
            'Single Bed' => $row['single_bed'] ?? null,
            'Double Bed' => $row['double_bed'] ?? null,
            'Mattress' => $row['mattress'] ?? null,
            'Sofa Set' => $row['sofa_set'] ?? null,
            'Bed Sheet' => $row['bed_sheet'] ?? null,
            'Wardrobe' => $row['wardrobe'] ?? null,
            'Centre Table' => $row['centre_table'] ?? null,
            'Wooden Chair' => $row['wooden_chair'] ?? null,
            'Dinning Table' => $row['dinning_table'] ?? null,
            'Dinning Chair' => $row['dinning_chair'] ?? null,
            'Side Table' => $row['side_table'] ?? null,
            'Fridge' => $row['fridge'] ?? null,
            'Water Dispenser' => $row['water_dispenser'] ?? null,
            'Washing Machine' => $row['washing_machine'] ?? null,
            'Air Cooler' => $row['air_cooler'] ?? null,
            'A/C' => $row['ac'] ?? null,
            'LED' => $row['led'] ?? null,
            'Gyser' => $row['gyser'] ?? null,
            'Electric Kettle' => $row['electric_kettle'] ?? null,
            'Wifi Rtr' => $row['wifi_rtr'] ?? null,
            'Water Bottle' => $row['water_bottle'] ?? null,
            'LPG cylinder' => $row['lpg_cylinder'] ?? null,
            'Gas Stove' => $row['gas_stove'] ?? null,
            'Crockery' => $row['crockery'] ?? null,
            'Kitchen Cabinet' => $row['kitchen_cabinet'] ?? null,
            'Mug' => $row['mug'] ?? null,
            'Bucket' => $row['bucket'] ?? null,
            'Mirror' => $row['mirror'] ?? null,
            'Dustbin' => $row['dustbin'] ?? null,
        ];
    }

    private function csvToAssoc(string $csvText): array
    {
        $h = fopen('php://temp', 'r+');
        fwrite($h, $csvText);
        rewind($h);

        $headers = fgetcsv($h);
        if (!is_array($headers) || $headers === []) {
            fclose($h);
            return [];
        }

        $headers = array_map(fn ($x) => trim((string) $x), $headers);
        $rows = [];
        while (($line = fgetcsv($h)) !== false) {
            if ($line === [null] || $line === []) {
                continue;
            }
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = trim((string) ($line[$i] ?? ''));
            }
            $rows[] = $row;
        }
        fclose($h);

        return $rows;
    }

    private function nullable(mixed $value): ?string
    {
        $out = trim((string) ($value ?? ''));
        return $out === '' ? null : $out;
    }
}
