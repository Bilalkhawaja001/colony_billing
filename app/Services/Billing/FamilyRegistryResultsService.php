<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class FamilyRegistryResultsService
{
    private const REGISTRY_REQUIRED = ['CompanyID', 'Name', 'CNIC_No.', 'Department', 'Designation', 'Unit_ID'];

    public function familyDetailsContext(array $query): array
    {
        $monthCycle = trim((string) ($query['month_cycle'] ?? ''));
        $companyId = trim((string) ($query['company_id'] ?? ''));

        if ($companyId === '') {
            return ['_http' => 400, 'status' => 'error', 'error' => 'company_id is required'];
        }

        $emp = DB::table('employees_master')
            ->where('company_id', $companyId)
            ->first(['company_id', 'name', 'unit_id', 'colony_type', 'block_floor', 'room_no']);

        if (!$emp) {
            return ['_http' => 404, 'status' => 'error', 'error' => 'CompanyID not found'];
        }

        $category = null;
        if ($monthCycle !== '' && (string) ($emp->unit_id ?? '') !== '') {
            $room = DB::table('util_unit_room_snapshot')
                ->where('month_cycle', $monthCycle)
                ->where('unit_id', (string) $emp->unit_id)
                ->orderByDesc('id')
                ->first(['category']);
            $category = $room->category ?? null;
        }

        return [
            'status' => 'ok',
            'row' => [
                'company_id' => $emp->company_id,
                'employee_name' => $emp->name,
                'unit_id' => $emp->unit_id,
                'colony_type' => $emp->colony_type,
                'block_floor' => $emp->block_floor,
                'room_no' => $emp->room_no,
                'category' => $category,
            ],
        ];
    }

    public function familyDetails(array $query): array
    {
        $monthCycle = trim((string) ($query['month_cycle'] ?? ''));
        $companyId = trim((string) ($query['company_id'] ?? ''));

        $rows = DB::table('family_details')
            ->when($monthCycle !== '', fn ($q) => $q->where('month_cycle', $monthCycle))
            ->when($companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->orderByDesc('month_cycle')
            ->orderBy('company_id')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        $byPair = [];
        foreach ($rows as $row) {
            $key = $row['month_cycle'].'|'.$row['company_id'];
            $byPair[$key] = [];
        }

        if ($byPair !== []) {
            $childrenRows = DB::table('family_child_details')
                ->where(function ($q) use ($rows) {
                    foreach ($rows as $row) {
                        $q->orWhere(function ($w) use ($row) {
                            $w->where('month_cycle', $row['month_cycle'])
                                ->where('company_id', $row['company_id']);
                        });
                    }
                })
                ->orderByDesc('month_cycle')
                ->orderBy('company_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($childrenRows as $child) {
                $key = $child->month_cycle.'|'.$child->company_id;
                $byPair[$key][] = (array) $child;
            }
        }

        foreach ($rows as &$row) {
            $row['children'] = $byPair[$row['month_cycle'].'|'.$row['company_id']] ?? [];
        }

        return ['status' => 'ok', 'rows' => $rows];
    }

    public function familyDetailsUpsert(array $data): array
    {
        $monthCycle = trim((string) ($data['month_cycle'] ?? ''));
        $companyId = trim((string) ($data['company_id'] ?? ''));

        if ($monthCycle === '' || $companyId === '') {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle and company_id are required'];
        }

        $children = is_array($data['children'] ?? null) ? $data['children'] : [];
        $parsedChildren = [];
        $childrenCount = 0;
        $schoolGoingChildren = 0;
        $vanUsingChildren = 0;

        foreach ($children as $idx => $raw) {
            $item = is_array($raw) ? $raw : [];
            $name = trim((string) ($item['child_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $ageRaw = $item['age'] ?? null;
            $age = ($ageRaw === null || $ageRaw === '') ? null : (int) $ageRaw;
            if ($age !== null && $age < 0) {
                return ['_http' => 400, 'status' => 'error', 'error' => 'child age cannot be negative at row '.($idx + 1)];
            }

            $schoolGoing = $this->truthy($item['school_going'] ?? false);
            $schoolName = $this->nullableTrim($item['school_name'] ?? null);
            $className = $this->nullableTrim($item['class_name'] ?? null);
            $vanUsingChild = $this->truthy($item['van_using_child'] ?? false);

            if ($schoolGoing && (!$schoolName || !$className)) {
                return ['_http' => 400, 'status' => 'error', 'error' => 'school_name and class_name required when school_going=Yes (child row '.($idx + 1).')'];
            }

            $childrenCount++;
            $schoolGoingChildren += $schoolGoing ? 1 : 0;
            $vanUsingChildren += $vanUsingChild ? 1 : 0;

            $parsedChildren[] = [
                'child_name' => $name,
                'age' => $age,
                'school_going' => $schoolGoing ? 1 : 0,
                'school_name' => $schoolName,
                'class_name' => $className,
                'van_using_child' => $vanUsingChild ? 1 : 0,
                'sort_order' => $idx + 1,
            ];
        }

        $spouseName = $this->nullableTrim($data['spouse_name'] ?? null);
        $spouseCount = $spouseName ? 1 : 0;
        $vanUsingAdults = max(0, (int) ($data['van_using_adults'] ?? 0));

        DB::transaction(function () use ($data, $monthCycle, $companyId, $spouseName, $spouseCount, $childrenCount, $schoolGoingChildren, $vanUsingChildren, $vanUsingAdults, $parsedChildren) {
            DB::table('family_details')->updateOrInsert(
                ['month_cycle' => $monthCycle, 'company_id' => $companyId],
                [
                    'employee_name' => $this->nullableTrim($data['employee_name'] ?? null),
                    'unit_id' => $this->nullableTrim($data['unit_id'] ?? null),
                    'category' => $this->nullableTrim($data['category'] ?? null),
                    'colony_type' => $this->nullableTrim($data['colony_type'] ?? null),
                    'block_floor' => $this->nullableTrim($data['block_floor'] ?? null),
                    'room_no' => $this->nullableTrim($data['room_no'] ?? null),
                    'spouse_name' => $spouseName,
                    'school_name' => null,
                    'class_name' => null,
                    'age' => null,
                    'spouse_count' => $spouseCount,
                    'children_count' => $childrenCount,
                    'school_going_children' => $schoolGoingChildren,
                    'van_using_children' => $vanUsingChildren,
                    'van_using_adults' => $vanUsingAdults,
                    'van_trips_per_day' => 1,
                    'deduction_mode' => 'None',
                    'deduction_amount' => 0,
                    'effective_from' => $this->nullableTrim($data['effective_from'] ?? null),
                    'effective_to' => $this->nullableTrim($data['effective_to'] ?? null),
                    'remarks' => $this->nullableTrim($data['remarks'] ?? null),
                    'updated_by' => $this->nullableTrim($data['updated_by'] ?? null),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('family_child_details')->where('month_cycle', $monthCycle)->where('company_id', $companyId)->delete();
            foreach ($parsedChildren as $child) {
                DB::table('family_child_details')->insert($child + [
                    'month_cycle' => $monthCycle,
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return ['status' => 'ok', 'month_cycle' => $monthCycle, 'company_id' => $companyId, 'children_saved' => count($parsedChildren)];
    }

    public function registryEmployeesUpsert(array $data): array
    {
        $companyId = trim((string) ($data['CompanyID'] ?? ''));
        if ($companyId === '') {
            return ['_http' => 400, 'status' => 'error', 'error' => 'CompanyID required'];
        }

        DB::table('employees_registry')->updateOrInsert(
            ['company_id' => $companyId],
            [
                'name' => $this->nullableTrim($data['Name'] ?? null),
                'cnic_no' => $this->nullableTrim($data['CNIC_No.'] ?? null),
                'department' => $this->nullableTrim($data['Department'] ?? null),
                'designation' => $this->nullableTrim($data['Designation'] ?? null),
                'unit_id' => $this->nullableTrim($data['Unit_ID'] ?? null),
                'father_name' => $this->nullableTrim($data["Father's Name"] ?? null),
                'mobile_no' => $this->nullableTrim($data['Mobile_No.'] ?? null),
                'colony_type' => $this->nullableTrim($data['Colony Type'] ?? null),
                'block_floor' => $this->nullableTrim($data['Block Floor'] ?? null),
                'room_no' => $this->nullableTrim($data['Room No'] ?? null),
                'active' => $this->nullableTrim($data['Active'] ?? 'Yes') ?? 'Yes',
                'remarks' => $this->nullableTrim($data['Remarks'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return ['status' => 'ok', 'CompanyID' => $companyId];
    }

    public function registryEmployeeGet(string $companyId): array
    {
        $row = DB::table('employees_registry')->where('company_id', $companyId)->first();
        if (!$row) {
            return ['_http' => 404, 'status' => 'error', 'error' => 'CompanyID not found in registry'];
        }
        return ['status' => 'ok', 'row' => (array) $row];
    }

    public function registryEmployeesImportPreview(string $csvText): array
    {
        if (trim($csvText) === '') {
            return ['_http' => 400, 'status' => 'error', 'error' => 'csv_text is required'];
        }

        [$accepted, $errors] = $this->parseRegistryCsv($csvText, false);

        return [
            'status' => 'ok',
            'total_rows' => count($accepted) + count($errors),
            'accepted_rows' => count($accepted),
            'rejected_rows' => count($errors),
            'accepted_preview' => array_slice($accepted, 0, 20),
            'errors_preview' => array_slice($errors, 0, 50),
            'error_report_download' => null,
        ];
    }

    public function registryEmployeesImportCommit(string $csvText): array
    {
        if (trim($csvText) === '') {
            return ['_http' => 400, 'status' => 'error', 'error' => 'csv_text is required'];
        }

        [$accepted] = $this->parseRegistryCsv($csvText, true);

        $inserted = 0;
        $updated = 0;

        foreach ($accepted as $item) {
            $row = $item['row'];
            $companyId = $row['CompanyID'];
            $exists = DB::table('employees_registry')->where('company_id', $companyId)->exists();
            $this->registryEmployeesUpsert($row);
            if ($exists) {
                $updated++;
            } else {
                $inserted++;
            }
        }

        $rejected = max(0, $this->csvDataRowCount($csvText) - count($accepted));
        return ['status' => 'ok', 'inserted' => $inserted, 'updated' => $updated, 'rejected' => $rejected];
    }

    public function registryEmployeesPromoteToMaster(bool $upsert): array
    {
        $rows = DB::table('employees_registry')->orderBy('company_id')->get();

        $promoted = 0;
        $skipped = 0;
        $rejected = 0;

        foreach ($rows as $row) {
            $r = (array) $row;
            $requiredMissing = array_filter([
                'CompanyID' => $r['company_id'] ?? '',
                'Name' => $r['name'] ?? '',
                'CNIC_No.' => $r['cnic_no'] ?? '',
                'Department' => $r['department'] ?? '',
                'Designation' => $r['designation'] ?? '',
                'Unit_ID' => $r['unit_id'] ?? '',
            ], fn ($v) => trim((string) $v) === '');

            if ($requiredMissing || !DB::table('util_unit')->where('unit_id', $r['unit_id'])->exists()) {
                $rejected++;
                continue;
            }

            $existsMaster = DB::table('employees_master')->where('company_id', $r['company_id'])->exists();
            if ($existsMaster && !$upsert) {
                $skipped++;
                continue;
            }

            DB::table('employees_master')->updateOrInsert(
                ['company_id' => $r['company_id']],
                [
                    'name' => $r['name'],
                    'department' => $r['department'],
                    'designation' => $r['designation'],
                    'unit_id' => $r['unit_id'],
                    'colony_type' => $r['colony_type'],
                    'block_floor' => $r['block_floor'],
                    'room_no' => $r['room_no'],
                    'active' => $r['active'] ?: 'Yes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $promoted++;
        }

        return [
            'status' => 'ok',
            'promoted' => $promoted,
            'skipped_existing' => $skipped,
            'rejected' => $rejected,
            'upsert' => $upsert,
        ];
    }

    public function resultsEmployeeWise(string $monthCycle): array
    {
        $m = trim($monthCycle);
        $rows = DB::table('billing_rows')
            ->selectRaw('company_id, unit_id, ROUND(water_amt,2) AS water_amt, ROUND(power_amt,2) AS power_amt, ROUND(drink_amt,2) AS drink_amt, ROUND(total_amt,2) AS total_amt')
            ->where('month_cycle', $m)
            ->orderBy('company_id')
            ->get();

        return ['status' => 'ok', 'month_cycle' => $m, 'rows' => $rows];
    }

    public function resultsUnitWise(string $monthCycle): array
    {
        $m = trim($monthCycle);
        $rows = DB::table('billing_rows')
            ->selectRaw('unit_id, ROUND(SUM(water_amt),2) AS water_amt, ROUND(SUM(power_amt),2) AS power_amt, ROUND(SUM(drink_amt),2) AS drink_amt, ROUND(SUM(total_amt),2) AS total_amt')
            ->where('month_cycle', $m)
            ->groupBy('unit_id')
            ->orderBy('unit_id')
            ->get();

        return ['status' => 'ok', 'month_cycle' => $m, 'rows' => $rows];
    }

    public function logs(string $monthCycle): array
    {
        $m = trim($monthCycle);
        $rows = DB::table('logs')
            ->select(['severity', 'code', 'message', 'ref_json', 'created_at'])
            ->where('month_cycle', $m)
            ->orderBy('id')
            ->get();

        return ['status' => 'ok', 'month_cycle' => $m, 'rows' => $rows];
    }

    private function parseRegistryCsv(string $csvText, bool $commit): array
    {
        $rows = $this->csvToAssoc($csvText);
        $seen = [];
        $accepted = [];
        $errors = [];

        foreach ($rows as $idx => $row) {
            $rowNo = $idx + 2;
            $miss = array_values(array_filter(self::REGISTRY_REQUIRED, fn ($f) => trim((string) ($row[$f] ?? '')) === ''));
            if ($miss !== []) {
                $errors[] = ['row_no' => $rowNo, 'error_code' => 'MISSING_REQUIRED', 'error_message' => 'Missing: '.implode(', ', $miss)];
                continue;
            }

            $cid = trim((string) $row['CompanyID']);
            if (isset($seen[$cid])) {
                $errors[] = ['row_no' => $rowNo, 'error_code' => 'DUPLICATE_IN_FILE', 'error_message' => 'Duplicate CompanyID in file: '.$cid];
                continue;
            }
            $seen[$cid] = true;

            if (!DB::table('util_unit')->where('unit_id', trim((string) $row['Unit_ID']))->exists()) {
                $errors[] = ['row_no' => $rowNo, 'error_code' => 'INVALID_UNIT_ID', 'error_message' => 'Unit_ID not found: '.trim((string) $row['Unit_ID'])];
                continue;
            }

            $entry = ['row_no' => $rowNo, 'CompanyID' => $cid, 'exists_in_master' => DB::table('employees_master')->where('company_id', $cid)->exists(), 'row' => $row];
            $accepted[] = $entry;
        }

        return [$accepted, $errors];
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

    private function csvDataRowCount(string $csvText): int
    {
        return count($this->csvToAssoc($csvText));
    }

    private function nullableTrim(mixed $value): ?string
    {
        $out = trim((string) ($value ?? ''));
        return $out === '' ? null : $out;
    }

    private function truthy(mixed $value): bool
    {
        $v = strtolower(trim((string) $value));
        return in_array($v, ['1', 'true', 'yes'], true);
    }
}
