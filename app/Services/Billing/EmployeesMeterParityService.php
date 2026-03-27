<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class EmployeesMeterParityService
{
    public function employees(array $query): array
    {
        $q = trim((string) ($query['q'] ?? ''));
        $department = trim((string) ($query['department'] ?? ''));
        $active = trim((string) ($query['active'] ?? ''));

        $rows = DB::table('employees_master')
            ->when($q !== '', function ($builder) use ($q) {
                $like = "%{$q}%";
                $builder->where(function ($w) use ($like) {
                    $w->where('company_id', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('department', 'like', $like)
                        ->orWhere('designation', 'like', $like)
                        ->orWhere('unit_id', 'like', $like);
                });
            })
            ->when($department !== '', fn ($b) => $b->where('department', $department))
            ->when($active !== '', fn ($b) => $b->where('active', $active))
            ->orderBy('company_id')
            ->get();

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
                    ->orWhere('department', 'like', $like)
                    ->orWhere('designation', 'like', $like);
            })
            ->orderBy('company_id')
            ->limit(100)
            ->get(['company_id', 'name', 'department', 'designation', 'unit_id', 'active']);

        return ['status' => 'ok', 'rows' => $rows];
    }

    public function employeeGet(string $companyId): array
    {
        $row = DB::table('employees_master')->where('company_id', $companyId)->first();
        if (!$row) {
            return ['status' => 'error', 'error' => 'CompanyID not found', '_http' => 404];
        }

        return ['status' => 'ok', 'row' => $row];
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

        $lines = preg_split('/\r\n|\r|\n/', $csvText) ?: [];
        if (count($lines) < 2) {
            return ['status' => 'error', 'error' => 'csv_text must include header and at least one row', '_http' => 400];
        }

        $header = str_getcsv(array_shift($lines));
        $map = [];
        foreach ($header as $i => $col) {
            $map[strtolower(trim((string) $col))] = $i;
        }

        $companyIdx = $map['company_id'] ?? $map['companyid'] ?? null;
        $nameIdx = $map['name'] ?? null;
        if ($companyIdx === null || $nameIdx === null) {
            return ['status' => 'error', 'error' => 'csv header must include company_id and name', '_http' => 400];
        }

        $inserted = 0;
        $updated = 0;

        DB::transaction(function () use ($lines, $map, $companyIdx, $nameIdx, &$inserted, &$updated) {
            foreach ($lines as $line) {
                if (trim((string) $line) === '') {
                    continue;
                }
                $cols = str_getcsv($line);
                $companyId = trim((string) ($cols[$companyIdx] ?? ''));
                $name = trim((string) ($cols[$nameIdx] ?? ''));
                if ($companyId === '' || $name === '') {
                    continue;
                }

                $exists = DB::table('employees_master')->where('company_id', $companyId)->exists();

                DB::table('employees_master')->updateOrInsert(
                    ['company_id' => $companyId],
                    [
                        'name' => $name,
                        'department' => $this->csvColumn($cols, $map, ['department']),
                        'designation' => $this->csvColumn($cols, $map, ['designation']),
                        'unit_id' => $this->csvColumn($cols, $map, ['unit_id', 'unitid']),
                        'colony_type' => $this->csvColumn($cols, $map, ['colony_type']),
                        'block_floor' => $this->csvColumn($cols, $map, ['block_floor']),
                        'room_no' => $this->csvColumn($cols, $map, ['room_no']),
                        'active' => $this->csvColumn($cols, $map, ['active']) ?? 'Yes',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                if ($exists) {
                    $updated++;
                } else {
                    $inserted++;
                }
            }
        });

        return ['status' => 'ok', 'inserted' => $inserted, 'updated' => $updated];
    }

    public function employeesUpsert(array $payload): array
    {
        $companyId = trim((string) ($payload['company_id'] ?? $payload['CompanyID'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));

        if ($companyId === '' || $name === '') {
            return ['status' => 'error', 'error' => 'company_id and name are required', '_http' => 400];
        }

        DB::table('employees_master')->updateOrInsert(
            ['company_id' => $companyId],
            [
                'name' => $name,
                'department' => $this->nullable($payload['department'] ?? null),
                'designation' => $this->nullable($payload['designation'] ?? null),
                'unit_id' => $this->nullable($payload['unit_id'] ?? null),
                'colony_type' => $this->nullable($payload['colony_type'] ?? null),
                'block_floor' => $this->nullable($payload['block_floor'] ?? null),
                'room_no' => $this->nullable($payload['room_no'] ?? null),
                'active' => trim((string) ($payload['active'] ?? 'Yes')) ?: 'Yes',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return ['status' => 'ok', 'company_id' => $companyId];
    }

    public function employeesAdd(array $payload): array
    {
        $companyId = trim((string) ($payload['company_id'] ?? $payload['CompanyID'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));
        if ($companyId === '' || $name === '') {
            return ['status' => 'error', 'error' => 'company_id and name are required', '_http' => 400];
        }
        if (DB::table('employees_master')->where('company_id', $companyId)->exists()) {
            return ['status' => 'error', 'error' => 'CompanyID already exists', '_http' => 409];
        }

        return $this->employeesUpsert($payload);
    }

    public function employeePatch(string $companyId, array $payload): array
    {
        if (!DB::table('employees_master')->where('company_id', $companyId)->exists()) {
            return ['status' => 'error', 'error' => 'CompanyID not found', '_http' => 404];
        }

        $updates = [];
        foreach (['name', 'department', 'designation', 'unit_id', 'colony_type', 'block_floor', 'room_no', 'active'] as $field) {
            if (array_key_exists($field, $payload)) {
                $updates[$field] = $this->nullable($payload[$field]);
            }
        }

        if (empty($updates)) {
            return ['status' => 'error', 'error' => 'No valid patch fields provided', '_http' => 400];
        }

        $updates['updated_at'] = now();
        DB::table('employees_master')->where('company_id', $companyId)->update($updates);

        return ['status' => 'ok', 'company_id' => $companyId];
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

        return ['status' => 'ok', 'company_id' => $companyId, 'policy' => 'soft-delete'];
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

    private function nullable(mixed $value): ?string
    {
        $out = trim((string) ($value ?? ''));
        return $out === '' ? null : $out;
    }

    private function csvColumn(array $cols, array $map, array $names): ?string
    {
        foreach ($names as $name) {
            if (array_key_exists($name, $map)) {
                return $this->nullable($cols[$map[$name]] ?? null);
            }
        }

        return null;
    }
}
