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

        $profileMap = [];
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

            $companyIds = array_values(array_unique(array_map(fn ($row) => $row['company_id'], $rows)));
            if ($companyIds !== []) {
                $profiles = DB::table('family_child_profiles')
                    ->whereIn('company_id', $companyIds)
                    ->orderBy('company_id')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                foreach ($profiles as $profile) {
                    $profileMap[$profile->company_id.'|'.mb_strtolower(trim((string) $profile->child_name))] = (array) $profile;
                }
            }

            foreach ($childrenRows as $child) {
                $row = (array) $child;
                $profileKey = $child->company_id.'|'.mb_strtolower(trim((string) $child->child_name));
                $profile = $profileMap[$profileKey] ?? null;
                if ($profile) {
                    $row['child_profile_id'] = $profile['id'] ?? null;
                    $row['class_grade'] = $profile['class_grade'] ?? null;
                    $row['van_using'] = $profile['van_using'] ?? null;
                    $row['transport_join_date'] = $profile['transport_join_date'] ?? null;
                    $row['transport_leave_date'] = $profile['transport_leave_date'] ?? null;
                    $row['default_route_label'] = $profile['default_route_label'] ?? null;
                    $row['profile_notes'] = $profile['notes'] ?? null;
                    $row['profile_is_active'] = $profile['is_active'] ?? null;
                }
                $key = $child->month_cycle.'|'.$child->company_id;
                $byPair[$key][] = $row;
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
            $transportJoinDate = $this->nullableTrim($item['transport_join_date'] ?? null);
            $transportLeaveDate = $this->nullableTrim($item['transport_leave_date'] ?? null);
            $defaultRouteLabel = $this->nullableTrim($item['default_route_label'] ?? null);
            $profileNotes = $this->nullableTrim($item['notes'] ?? null);
            $profileId = isset($item['child_profile_id']) && $item['child_profile_id'] !== '' ? (int) $item['child_profile_id'] : null;

            if ($schoolGoing && (!$schoolName || !$className)) {
                return ['_http' => 400, 'status' => 'error', 'error' => 'school_name and class_name required when school_going=Yes (child row '.($idx + 1).')'];
            }

            $childrenCount++;
            $schoolGoingChildren += $schoolGoing ? 1 : 0;
            $vanUsingChildren += $vanUsingChild ? 1 : 0;

            $parsedChildren[] = [
                'child_profile_id' => $profileId,
                'child_name' => $name,
                'age' => $age,
                'school_going' => $schoolGoing ? 1 : 0,
                'school_name' => $schoolName,
                'class_name' => $className,
                'van_using_child' => $vanUsingChild ? 1 : 0,
                'class_grade' => $className,
                'van_using' => $vanUsingChild ? 1 : 0,
                'transport_join_date' => $transportJoinDate,
                'transport_leave_date' => $transportLeaveDate,
                'default_route_label' => $defaultRouteLabel,
                'notes' => $profileNotes,
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
                $profilePayload = [
                    'company_id' => $companyId,
                    'child_name' => $child['child_name'],
                    'school_name' => $child['school_name'],
                    'class_grade' => $child['class_grade'],
                    'school_going' => $child['school_going'],
                    'van_using' => $child['van_using'],
                    'transport_join_date' => $child['transport_join_date'],
                    'transport_leave_date' => $child['transport_leave_date'],
                    'default_route_label' => $child['default_route_label'],
                    'is_active' => 1,
                    'sort_order' => $child['sort_order'],
                    'notes' => $child['notes'],
                    'updated_at' => now(),
                ];

                if (!empty($child['child_profile_id'])) {
                    DB::table('family_child_profiles')->where('id', $child['child_profile_id'])->update($profilePayload);
                    $profileId = (int) $child['child_profile_id'];
                } else {
                    $matchedProfileId = DB::table('family_child_profiles')
                        ->where('company_id', $companyId)
                        ->whereRaw('LOWER(TRIM(child_name)) = ?', [mb_strtolower(trim((string) $child['child_name']))])
                        ->value('id');

                    if ($matchedProfileId) {
                        DB::table('family_child_profiles')->where('id', $matchedProfileId)->update($profilePayload);
                        $profileId = (int) $matchedProfileId;
                    } else {
                        $profileId = DB::table('family_child_profiles')->insertGetId($profilePayload + [
                            'created_at' => now(),
                        ]);
                    }
                }

                DB::table('family_child_details')->insert([
                    'month_cycle' => $monthCycle,
                    'company_id' => $companyId,
                    'child_name' => $child['child_name'],
                    'age' => $child['age'],
                    'school_going' => $child['school_going'],
                    'school_name' => $child['school_name'],
                    'class_name' => $child['class_name'],
                    'van_using_child' => $child['van_using_child'],
                    'sort_order' => $child['sort_order'],
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
                'section' => $this->nullableTrim($data['Section'] ?? null),
                'sub_section' => $this->nullableTrim($data['Sub Section'] ?? null),
                'designation' => $this->nullableTrim($data['Designation'] ?? null),
                'employee_type' => $this->nullableTrim($data['Employee Type'] ?? null),
                'join_date' => $this->nullableTrim($data['Join Date'] ?? null),
                'leave_date' => $this->nullableTrim($data['Leave Date'] ?? null),
                'unit_id' => $this->nullableTrim($data['Unit_ID'] ?? null),
                'father_name' => $this->nullableTrim($data["Father's Name"] ?? null),
                'mobile_no' => $this->nullableTrim($data['Mobile_No.'] ?? null),
                'colony_type' => $this->nullableTrim($data['Colony Type'] ?? null),
                'block_floor' => $this->nullableTrim($data['Block Floor'] ?? null),
                'room_no' => $this->nullableTrim($data['Room No'] ?? null),
                'shared_room' => $this->nullableTrim($data['Shared Room'] ?? null),
                'active' => $this->nullableTrim($data['Active'] ?? 'Yes') ?? 'Yes',
                'remarks' => $this->nullableTrim($data['Remarks'] ?? null),
                'iron_cot' => $this->nullableTrim($data['Iron Cot'] ?? null),
                'single_bed' => $this->nullableTrim($data['Single Bed'] ?? null),
                'double_bed' => $this->nullableTrim($data['Double Bed'] ?? null),
                'mattress' => $this->nullableTrim($data['Mattress'] ?? null),
                'sofa_set' => $this->nullableTrim($data['Sofa Set'] ?? null),
                'bed_sheet' => $this->nullableTrim($data['Bed Sheet'] ?? null),
                'wardrobe' => $this->nullableTrim($data['Wardrobe'] ?? null),
                'centre_table' => $this->nullableTrim($data['Centre Table'] ?? null),
                'wooden_chair' => $this->nullableTrim($data['Wooden Chair'] ?? null),
                'dinning_table' => $this->nullableTrim($data['Dinning Table'] ?? null),
                'dinning_chair' => $this->nullableTrim($data['Dinning Chair'] ?? null),
                'side_table' => $this->nullableTrim($data['Side Table'] ?? null),
                'fridge' => $this->nullableTrim($data['Fridge'] ?? null),
                'water_dispenser' => $this->nullableTrim($data['Water Dispenser'] ?? null),
                'washing_machine' => $this->nullableTrim($data['Washing Machine'] ?? null),
                'air_cooler' => $this->nullableTrim($data['Air Cooler'] ?? null),
                'ac' => $this->nullableTrim($data['A/C'] ?? null),
                'led' => $this->nullableTrim($data['LED'] ?? null),
                'gyser' => $this->nullableTrim($data['Gyser'] ?? null),
                'electric_kettle' => $this->nullableTrim($data['Electric Kettle'] ?? null),
                'wifi_rtr' => $this->nullableTrim($data['Wifi Rtr'] ?? null),
                'water_bottle' => $this->nullableTrim($data['Water Bottle'] ?? null),
                'lpg_cylinder' => $this->nullableTrim($data['LPG cylinder'] ?? null),
                'gas_stove' => $this->nullableTrim($data['Gas Stove'] ?? null),
                'crockery' => $this->nullableTrim($data['Crockery'] ?? null),
                'kitchen_cabinet' => $this->nullableTrim($data['Kitchen Cabinet'] ?? null),
                'mug' => $this->nullableTrim($data['Mug'] ?? null),
                'bucket' => $this->nullableTrim($data['Bucket'] ?? null),
                'mirror' => $this->nullableTrim($data['Mirror'] ?? null),
                'dustbin' => $this->nullableTrim($data['Dustbin'] ?? null),
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

        [$accepted, $errors, $meta] = $this->parseRegistryCsv($csvText, false);

        return [
            'status' => 'ok',
            'mode' => 'preview',
            'total_rows' => $meta['total_rows'],
            'valid_rows' => count($accepted),
            'invalid_rows' => count($errors),
            'skipped_rows' => count($errors),
            'inserted_rows' => 0,
            'updated_rows' => 0,
            'rejected_rows' => count($errors),
            'accepted_rows' => count($accepted),
            'accepted_preview' => array_slice($accepted, 0, 100),
            'errors_preview' => array_slice($errors, 0, 100),
            'reject_report' => array_slice($errors, 0, 1000),
            'error_report_download' => null,
        ];
    }

    public function registryEmployeesImportCommit(string $csvText): array
    {
        if (trim($csvText) === '') {
            return ['_http' => 400, 'status' => 'error', 'error' => 'csv_text is required'];
        }

        [$accepted, $errors, $meta] = $this->parseRegistryCsv($csvText, true);

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

        return [
            'status' => 'ok',
            'mode' => 'commit',
            'total_rows' => $meta['total_rows'],
            'valid_rows' => count($accepted),
            'invalid_rows' => count($errors),
            'skipped_rows' => count($errors),
            'inserted_rows' => $inserted,
            'updated_rows' => $updated,
            'rejected_rows' => count($errors),
            'inserted' => $inserted,
            'updated' => $updated,
            'rejected' => count($errors),
            'accepted_rows' => count($accepted),
            'errors_preview' => array_slice($errors, 0, 100),
            'reject_report' => array_slice($errors, 0, 1000),
        ];
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
                    'father_name' => $r['father_name'] ?? null,
                    'cnic_no' => $r['cnic_no'] ?? null,
                    'mobile_no' => $r['mobile_no'] ?? null,
                    'department' => $r['department'],
                    'section' => $r['section'] ?? null,
                    'sub_section' => $r['sub_section'] ?? null,
                    'designation' => $r['designation'],
                    'employee_type' => $r['employee_type'] ?? null,
                    'join_date' => $r['join_date'] ?? null,
                    'leave_date' => $r['leave_date'] ?? null,
                    'unit_id' => $r['unit_id'],
                    'colony_type' => $r['colony_type'],
                    'block_floor' => $r['block_floor'],
                    'room_no' => $r['room_no'],
                    'shared_room' => $r['shared_room'] ?? null,
                    'active' => $r['active'] ?: 'Yes',
                    'remarks' => $r['remarks'] ?? null,
                    'iron_cot' => $r['iron_cot'] ?? null,
                    'single_bed' => $r['single_bed'] ?? null,
                    'double_bed' => $r['double_bed'] ?? null,
                    'mattress' => $r['mattress'] ?? null,
                    'sofa_set' => $r['sofa_set'] ?? null,
                    'bed_sheet' => $r['bed_sheet'] ?? null,
                    'wardrobe' => $r['wardrobe'] ?? null,
                    'centre_table' => $r['centre_table'] ?? null,
                    'wooden_chair' => $r['wooden_chair'] ?? null,
                    'dinning_table' => $r['dinning_table'] ?? null,
                    'dinning_chair' => $r['dinning_chair'] ?? null,
                    'side_table' => $r['side_table'] ?? null,
                    'fridge' => $r['fridge'] ?? null,
                    'water_dispenser' => $r['water_dispenser'] ?? null,
                    'washing_machine' => $r['washing_machine'] ?? null,
                    'air_cooler' => $r['air_cooler'] ?? null,
                    'ac' => $r['ac'] ?? null,
                    'led' => $r['led'] ?? null,
                    'gyser' => $r['gyser'] ?? null,
                    'electric_kettle' => $r['electric_kettle'] ?? null,
                    'wifi_rtr' => $r['wifi_rtr'] ?? null,
                    'water_bottle' => $r['water_bottle'] ?? null,
                    'lpg_cylinder' => $r['lpg_cylinder'] ?? null,
                    'gas_stove' => $r['gas_stove'] ?? null,
                    'crockery' => $r['crockery'] ?? null,
                    'kitchen_cabinet' => $r['kitchen_cabinet'] ?? null,
                    'mug' => $r['mug'] ?? null,
                    'bucket' => $r['bucket'] ?? null,
                    'mirror' => $r['mirror'] ?? null,
                    'dustbin' => $r['dustbin'] ?? null,
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
            if ($this->isBlankCsvRow($row)) {
                $errors[] = ['row_no' => $rowNo, 'CompanyID' => '', 'error_code' => 'BLANK_ROW', 'error_message' => 'blank row'];
                continue;
            }

            $miss = array_values(array_filter(self::REGISTRY_REQUIRED, fn ($f) => trim((string) ($row[$f] ?? '')) === ''));
            if ($miss !== []) {
                $errors[] = ['row_no' => $rowNo, 'CompanyID' => trim((string) ($row['CompanyID'] ?? '')), 'error_code' => 'MISSING_REQUIRED', 'error_message' => 'Missing: '.implode(', ', $miss)];
                continue;
            }

            $cid = trim((string) $row['CompanyID']);
            if ($cid === '') {
                $errors[] = ['row_no' => $rowNo, 'CompanyID' => '', 'error_code' => 'MISSING_COMPANY_ID', 'error_message' => 'missing company_id'];
                continue;
            }
            if (isset($seen[$cid])) {
                $errors[] = ['row_no' => $rowNo, 'CompanyID' => $cid, 'error_code' => 'DUPLICATE_COMPANY_ID_IN_FILE', 'error_message' => 'duplicate company_id in uploaded file'];
                continue;
            }
            $seen[$cid] = true;

            if (DB::table('employees_registry')->where('company_id', $cid)->exists() || DB::table('employees_master')->where('company_id', $cid)->exists()) {
                $errors[] = ['row_no' => $rowNo, 'CompanyID' => $cid, 'error_code' => 'COMPANY_ID_ALREADY_EXISTS', 'error_message' => 'company_id already exists'];
                continue;
            }

            if (!DB::table('util_unit')->where('unit_id', trim((string) $row['Unit_ID']))->exists()) {
                $errors[] = ['row_no' => $rowNo, 'CompanyID' => $cid, 'error_code' => 'INVALID_UNIT_ID', 'error_message' => 'invalid format'];
                continue;
            }

            $entry = ['row_no' => $rowNo, 'CompanyID' => $cid, 'exists_in_master' => false, 'row' => $row];
            $accepted[] = $entry;
        }

        return [$accepted, $errors, ['total_rows' => count($rows)]];
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

    private function isBlankCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
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
