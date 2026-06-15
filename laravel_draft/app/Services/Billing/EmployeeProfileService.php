<?php

namespace App\Services\Billing;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class EmployeeProfileService
{
    private const ASSET_LABELS = [
        'iron_cot' => 'Iron Cot',
        'single_bed' => 'Single Bed',
        'double_bed' => 'Double Bed',
        'mattress' => 'Mattress',
        'sofa_set' => 'Sofa Set',
        'bed_sheet' => 'Bed Sheet',
        'wardrobe' => 'Wardrobe',
        'centre_table' => 'Centre Table',
        'wooden_chair' => 'Wooden Chair',
        'dinning_table' => 'Dinning Table',
        'dinning_chair' => 'Dinning Chair',
        'side_table' => 'Side Table',
        'fridge' => 'Fridge',
        'water_dispenser' => 'Water Dispenser',
        'washing_machine' => 'Washing Machine',
        'air_cooler' => 'Air Cooler',
        'ac' => 'A/C',
        'led' => 'LED',
        'gyser' => 'Gyser',
        'electric_kettle' => 'Electric Kettle',
        'wifi_rtr' => 'Wifi Router',
        'water_bottle' => 'Water Bottle',
        'lpg_cylinder' => 'LPG Cylinder',
        'gas_stove' => 'Gas Stove',
        'crockery' => 'Crockery',
        'kitchen_cabinet' => 'Kitchen Cabinet',
        'mug' => 'Mug',
        'bucket' => 'Bucket',
        'mirror' => 'Mirror',
        'dustbin' => 'Dustbin',
    ];

    public function profile(string $companyId): array
    {
        $companyId = trim($companyId);

        $employee = DB::table('employees_master')
            ->where('company_id', $companyId)
            ->first();

        if (!$employee) {
            return [
                '_http' => 404,
                'status' => 'error',
                'error' => 'Employee not found.',
            ];
        }

        $activeResidence = DB::table('employee_residence_assignments')
            ->where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        $hasHouseholdResidence = $activeResidence
            && strtoupper(trim((string) $activeResidence->occupancy_mode)) === 'HOUSEHOLD';

        $familyResidenceLabel = $hasHouseholdResidence
            ? (string) $activeResidence->unit_id
            : '';

        $familyResidenceStatus = $hasHouseholdResidence
            ? 'Active Family House'
            : 'Outside Colony / No Active Family House';

        $residenceHistory = DB::table('employee_residence_assignments')
            ->where('company_id', $companyId)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get([
                'id',
                'unit_id',
                'room_no',
                'residence_type',
                'occupancy_mode',
                'start_date',
                'end_date',
                'status',
                'start_reason',
                'closure_reason',
                'remarks',
                'created_by',
            ])
            ->map(fn ($row) => (array) $row)
            ->all();

        $latestRoomMonth = DB::table('util_unit_room_snapshot')->max('month_cycle');

        $residenceOptions = DB::table('util_unit_room_snapshot as r')
            ->leftJoin(DB::raw("(SELECT unit_id, room_no, COUNT(*) as active_assigned_count FROM employee_residence_assignments WHERE status = 'ACTIVE' AND end_date IS NULL GROUP BY unit_id, room_no) as occ"), function ($join) {
                $join->on('occ.unit_id', '=', 'r.unit_id')
                    ->on('occ.room_no', '=', 'r.room_no');
            })
            ->where('r.month_cycle', $latestRoomMonth)
            ->orderBy('r.residence_type')
            ->orderBy('r.unit_id')
            ->orderBy('r.room_no')
            ->get([
                'r.unit_id',
                'r.room_no',
                'r.residence_type',
                'r.category',
                'r.block_floor',
                DB::raw('COALESCE(occ.active_assigned_count, 0) as active_assigned_count'),
            ])
            ->map(function ($row) {
                $option = (array) $row;
                $assignedCount = (int) ($option['active_assigned_count'] ?? 0);
                $residenceType = (string) ($option['residence_type'] ?? '');
                $typeUpper = strtoupper(trim($residenceType));
                $isHouse = str_starts_with($typeUpper, 'HOUSE');
                $isEligibleResidence = $isHouse || in_array($typeUpper, ['BACHELOR', 'HOSTEL', 'CONTAINER'], true);

                $unitCode = strtoupper(trim((string) ($option['unit_id'] ?? '')));

                $option['active_assigned_count'] = $assignedCount;
                $option['dropdown_status'] = $assignedCount > 0 ? 'OCCUPIED' : 'VACANT';
                $option['is_blocked_for_assignment'] = (!$isEligibleResidence) || ($isHouse && $assignedCount > 0);
                $option['block_reason'] = !$isEligibleResidence ? 'non-residence item' : (($isHouse && $assignedCount > 0) ? 'already assigned' : '');

                if (str_starts_with($unitCode, 'W')) {
                    $option['unit_group'] = 'Weaving';
                } elseif (str_starts_with($unitCode, 'S')) {
                    $option['unit_group'] = 'Spinning';
                } else {
                    $option['unit_group'] = 'Centralized';
                }

                return $option;
            })
            ->all();

        $linkedMembers = DB::table('family_members')
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        $movementRows = DB::table('family_member_movements as m')
            ->join('family_members as f', 'f.id', '=', 'm.family_member_id')
            ->where('f.company_id', $companyId)
            ->orderByDesc('m.movement_date')
            ->orderByDesc('m.id')
            ->get([
                'm.id',
                'm.family_member_id',
                'f.member_name',
                'f.relation',
                'm.movement_type',
                'm.movement_date',
                'm.remarks',
                'm.created_by',
                'm.created_at',
            ])
            ->map(fn ($row) => (array) $row)
            ->all();

        $latestMovementByMember = [];

        foreach ($movementRows as $movement) {
            $memberId = (int) $movement['family_member_id'];

            if (!isset($latestMovementByMember[$memberId])) {
                $latestMovementByMember[$memberId] = $movement;
            }
        }

        $employeeActive = strtoupper(trim((string) ($employee->active ?? ''))) === 'YES';

        $familyRows = [[
            'member_id' => null,
            'member_name' => (string) $employee->name,
            'relation' => 'Family Head / Employee',
            'age' => null,
            'school_going' => null,
            'current_status' => $employeeActive ? 'ACTIVE' : 'INACTIVE',
            'current_house' => $familyResidenceLabel,
            'is_family_head' => true,
            'next_movement_type' => null,
            'next_action_label' => null,
            'latest_movement' => null,
        ]];

        foreach ($linkedMembers as $member) {
            $memberId = (int) $member['id'];
            $status = strtoupper(trim((string) ($member['current_status'] ?? 'PRESENT')));

            $familyRows[] = [
                'member_id' => $memberId,
                'member_name' => (string) ($member['member_name'] ?? ''),
                'relation' => (string) ($member['relation'] ?? ''),
                'age' => $member['age'] ?? null,
                'school_going' => $member['school_going'] ?? null,
                'school_name' => (string) ($member['school_name'] ?? ''),
                'class_name' => (string) ($member['class_name'] ?? ''),
                'remarks' => (string) ($member['remarks'] ?? ''),
                'current_status' => $status,
                'current_house' => $status === 'PRESENT' ? $familyResidenceLabel : '',
                'is_family_head' => false,
                'next_movement_type' => $status === 'PRESENT' ? 'MOVE_OUT' : ($status === 'MOVED_OUT' ? 'RETURN_BACK' : null),
                'next_action_label' => $status === 'PRESENT' ? 'Move Out' : ($status === 'MOVED_OUT' ? 'Move In' : null),
                'latest_movement' => $latestMovementByMember[$memberId] ?? null,
            ];
        }

        $assets = [];
        $totalAssetQuantity = 0.0;

        foreach (self::ASSET_LABELS as $field => $label) {
            $raw = trim((string) ($employee->{$field} ?? ''));

            if ($raw === '' || !is_numeric($raw) || (float) $raw <= 0) {
                continue;
            }

            $quantity = (float) $raw;
            $totalAssetQuantity += $quantity;

            $assets[] = [
                'label' => $label,
                'quantity' => fmod($quantity, 1.0) === 0.0 ? (int) $quantity : $quantity,
            ];
        }

        $presentLinkedMembers = count(array_filter(
            $linkedMembers,
            fn ($member) => strtoupper(trim((string) ($member['current_status'] ?? ''))) === 'PRESENT'
        ));

        return [
            'status' => 'ok',
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
                'join_date' => $employee->join_date ?? null,
                'active_label' => $employeeActive ? 'Active' : 'Inactive',
                'active' => $employeeActive,
            ],
            'residence' => [
                'unit_id' => $activeResidence ? (string) $activeResidence->unit_id : '',
                'residence_type' => $activeResidence ? (string) $activeResidence->residence_type : '',
                'category' => $activeResidence ? (string) ($activeResidence->category ?? '') : '',
                'colony_type' => $activeResidence ? (string) $activeResidence->residence_type : '',
                'block_floor' => $activeResidence ? (string) ($activeResidence->block_floor ?? '') : '',
                'room_no' => $activeResidence ? (string) $activeResidence->room_no : '',
                'occupancy_mode' => $activeResidence ? (string) $activeResidence->occupancy_mode : '',
                'start_date' => $activeResidence ? (string) $activeResidence->start_date : null,
                'status' => $activeResidence ? (string) $activeResidence->status : 'UNASSIGNED',
                'family_residence' => $familyResidenceLabel,
                'family_residence_status' => $familyResidenceStatus,
            ],
            'kpis' => [
                'total_family_members' => 1 + count($linkedMembers),
                'linked_family_members' => count($linkedMembers),
                'present_linked_members' => $presentLinkedMembers,
                'total_issued_assets' => fmod($totalAssetQuantity, 1.0) === 0.0 ? (int) $totalAssetQuantity : $totalAssetQuantity,
                'family_status' => $employeeActive && $presentLinkedMembers === count($linkedMembers) ? 'Active' : 'Review',
            ],
            'family_rows' => $familyRows,
            'family_movements' => $movementRows,
            'residence_history' => $residenceHistory,
            'residence_options' => $residenceOptions,
            'assets' => $assets,
        ];
    }

    public function createFamilyMember(string $companyId, array $payload): array
    {
        return $this->saveFamilyMember($companyId, null, $payload);
    }

    public function updateFamilyMember(string $companyId, int $familyMemberId, array $payload): array
    {
        return $this->saveFamilyMember($companyId, $familyMemberId, $payload);
    }

    private function saveFamilyMember(string $companyId, ?int $familyMemberId, array $payload): array
    {
        $companyId = trim($companyId);
        $memberName = trim((string) ($payload['member_name'] ?? ''));
        $relation = trim((string) ($payload['relation'] ?? ''));
        $ageRaw = trim((string) ($payload['age'] ?? ''));
        $schoolGoing = filter_var($payload['school_going'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $schoolName = trim((string) ($payload['school_name'] ?? ''));
        $className = trim((string) ($payload['class_name'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));

        if (!DB::table('employees_master')->where('company_id', $companyId)->exists()) {
            return ['_http' => 404, 'status' => 'error', 'error' => 'Employee not found.'];
        }

        if ($memberName === '' || mb_strlen($memberName) > 255) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Valid family member name is required.'];
        }

        if ($relation === '' || mb_strlen($relation) > 255) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Valid relation is required.'];
        }

        $age = null;

        if ($ageRaw !== '') {
            if (!is_numeric($ageRaw) || (float) $ageRaw < 0 || (float) $ageRaw > 150) {
                return ['_http' => 422, 'status' => 'error', 'error' => 'Age must be between 0 and 150.'];
            }

            $age = round((float) $ageRaw, 1);
        }

        if (mb_strlen($schoolName) > 255 || mb_strlen($className) > 255) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'School name or class is too long.'];
        }

        if (mb_strlen($remarks) > 1000) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Remarks cannot exceed 1000 characters.'];
        }

        if (!$schoolGoing) {
            $schoolName = '';
            $className = '';
        }

        $editableData = [
            'member_name' => $memberName,
            'relation' => $relation,
            'age' => $age,
            'school_going' => $schoolGoing,
            'school_name' => $schoolName === '' ? null : $schoolName,
            'class_name' => $className === '' ? null : $className,
            'remarks' => $remarks === '' ? null : $remarks,
            'updated_at' => now(),
        ];

        return DB::transaction(function () use ($companyId, $familyMemberId, $editableData) {
            if ($familyMemberId !== null) {
                $member = DB::table('family_members')
                    ->where('id', $familyMemberId)
                    ->where('company_id', $companyId)
                    ->lockForUpdate()
                    ->first();

                if (!$member) {
                    return ['_http' => 404, 'status' => 'error', 'error' => 'Family member not found for this employee.'];
                }

                DB::table('family_members')
                    ->where('id', $familyMemberId)
                    ->update($editableData);

                return [
                    'status' => 'ok',
                    'message' => 'Family member updated successfully.',
                    'family_member_id' => $familyMemberId,
                ];
            }

            $newId = DB::table('family_members')->insertGetId(array_merge($editableData, [
                'company_id' => $companyId,
                'source_month_cycle' => null,
                'source_residence_type' => null,
                'source_colony_building_name' => null,
                'source_block_floor' => null,
                'source_room_no' => null,
                'current_status' => 'PRESENT',
                'is_active' => true,
                'created_at' => now(),
            ]));

            return [
                'status' => 'ok',
                'message' => 'Family member added successfully.',
                'family_member_id' => $newId,
            ];
        });
    }

    public function assignResidence(string $companyId, array $payload): array
    {
        $companyId = trim($companyId);
        $dateResult = $this->validateResidenceDate($payload['effective_date'] ?? null);

        if (($dateResult['status'] ?? 'error') !== 'ok') {
            return $dateResult;
        }

        $unitId = trim((string) ($payload['unit_id'] ?? ''));
        $roomNo = trim((string) ($payload['room_no'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));
        $createdBy = trim((string) ($payload['created_by'] ?? ''));

        if ($unitId === '' || $roomNo === '') {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Unit and room are required.'];
        }

        if (mb_strlen($remarks) > 1000) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Remarks cannot exceed 1000 characters.'];
        }

        return DB::transaction(function () use ($companyId, $unitId, $roomNo, $dateResult, $remarks, $createdBy) {
            $employee = DB::table('employees_master')
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->first();

            if (!$employee) {
                return ['_http' => 404, 'status' => 'error', 'error' => 'Employee not found.'];
            }

            $active = DB::table('employee_residence_assignments')
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->first();

            if ($active) {
                return ['_http' => 409, 'status' => 'error', 'error' => 'Employee already has an active residence. Use Shift Residence.'];
            }

            $room = $this->findResidenceRoomForUpdate($unitId, $roomNo);

            if (!$room) {
                return ['_http' => 404, 'status' => 'error', 'error' => 'Selected residence room was not found.'];
            }

            if (!$this->isEligibleResidenceType((string) ($room->residence_type ?? ''))) {
                return ['_http' => 422, 'status' => 'error', 'error' => 'Selected room is not an employee residence category.'];
            }

            if ($this->isHouseResidence((string) $room->residence_type)
                && $this->hasActiveHouseOccupant($unitId, $roomNo)) {
                return ['_http' => 409, 'status' => 'error', 'error' => 'Selected house is already occupied.'];
            }

            $mode = $this->determineOccupancyMode($companyId, (string) $room->residence_type);

            $id = $this->insertResidenceAssignment(
                $companyId,
                $room,
                $mode,
                $dateResult['date'],
                'ASSIGN',
                $remarks,
                $createdBy
            );

            return [
                'status' => 'ok',
                'message' => 'Residence assigned successfully.',
                'assignment_id' => $id,
                'occupancy_mode' => $mode,
            ];
        });
    }

    public function shiftResidence(string $companyId, array $payload): array
    {
        $companyId = trim($companyId);
        $dateResult = $this->validateResidenceDate($payload['effective_date'] ?? null);

        if (($dateResult['status'] ?? 'error') !== 'ok') {
            return $dateResult;
        }

        $unitId = trim((string) ($payload['unit_id'] ?? ''));
        $roomNo = trim((string) ($payload['room_no'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));
        $createdBy = trim((string) ($payload['created_by'] ?? ''));

        if ($unitId === '' || $roomNo === '') {
            return ['_http' => 422, 'status' => 'error', 'error' => 'New unit and room are required.'];
        }

        if (mb_strlen($remarks) > 1000) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Remarks cannot exceed 1000 characters.'];
        }

        return DB::transaction(function () use ($companyId, $unitId, $roomNo, $dateResult, $remarks, $createdBy) {
            $active = DB::table('employee_residence_assignments')
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->first();

            if (!$active) {
                return ['_http' => 409, 'status' => 'error', 'error' => 'No active residence found. Use Assign Residence.'];
            }

            if ($dateResult['date'] <= (string) $active->start_date) {
                return ['_http' => 422, 'status' => 'error', 'error' => 'Shift date must be after the current assignment start date.'];
            }

            if ((string) $active->unit_id === $unitId && (string) $active->room_no === $roomNo) {
                return ['_http' => 409, 'status' => 'error', 'error' => 'New residence must be different from current residence.'];
            }

            $room = $this->findResidenceRoomForUpdate($unitId, $roomNo);

            if (!$room) {
                return ['_http' => 404, 'status' => 'error', 'error' => 'Selected residence room was not found.'];
            }

            if (!$this->isEligibleResidenceType((string) ($room->residence_type ?? ''))) {
                return ['_http' => 422, 'status' => 'error', 'error' => 'Selected room is not an employee residence category.'];
            }

            if ($this->isHouseResidence((string) $room->residence_type)
                && $this->hasActiveHouseOccupant($unitId, $roomNo, (int) $active->id)) {
                return ['_http' => 409, 'status' => 'error', 'error' => 'Selected house is already occupied.'];
            }

            $mode = $this->determineOccupancyMode($companyId, (string) $room->residence_type);

            $closureReason = strtoupper(trim((string) $active->occupancy_mode)) === 'HOUSEHOLD'
                && $mode !== 'HOUSEHOLD'
                ? 'FAMILY_SENT_BACK'
                : 'SHIFTED';

            $closedOn = Carbon::createFromFormat('Y-m-d', $dateResult['date'])
                ->subDay()
                ->toDateString();

            DB::table('employee_residence_assignments')
                ->where('id', $active->id)
                ->update([
                    'end_date' => $closedOn,
                    'status' => 'CLOSED',
                    'closure_reason' => $closureReason,
                    'updated_at' => now(),
                ]);

            $id = $this->insertResidenceAssignment(
                $companyId,
                $room,
                $mode,
                $dateResult['date'],
                'SHIFT',
                $remarks,
                $createdBy
            );

            return [
                'status' => 'ok',
                'message' => 'Residence shifted successfully.',
                'assignment_id' => $id,
                'old_assignment_id' => (int) $active->id,
                'occupancy_mode' => $mode,
                'closure_reason' => $closureReason,
            ];
        });
    }

    public function vacateResidence(string $companyId, array $payload): array
    {
        $companyId = trim($companyId);
        $dateResult = $this->validateResidenceDate($payload['effective_date'] ?? null);

        if (($dateResult['status'] ?? 'error') !== 'ok') {
            return $dateResult;
        }

        $remarks = trim((string) ($payload['remarks'] ?? ''));

        if (mb_strlen($remarks) > 1000) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Remarks cannot exceed 1000 characters.'];
        }

        return DB::transaction(function () use ($companyId, $dateResult, $remarks) {
            $active = DB::table('employee_residence_assignments')
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->first();

            if (!$active) {
                return ['_http' => 409, 'status' => 'error', 'error' => 'No active residence found to vacate.'];
            }

            if ($dateResult['date'] <= (string) $active->start_date) {
                return ['_http' => 422, 'status' => 'error', 'error' => 'Vacate date must be after the current assignment start date.'];
            }

            $closedOn = Carbon::createFromFormat('Y-m-d', $dateResult['date'])
                ->subDay()
                ->toDateString();

            $closureReason = strtoupper(trim((string) $active->occupancy_mode)) === 'HOUSEHOLD'
                ? 'FAMILY_SENT_BACK'
                : 'VACATED';

            DB::table('employee_residence_assignments')
                ->where('id', $active->id)
                ->update([
                    'end_date' => $closedOn,
                    'status' => 'CLOSED',
                    'closure_reason' => $closureReason,
                    'remarks' => $remarks === '' ? $active->remarks : $remarks,
                    'updated_at' => now(),
                ]);

            return [
                'status' => 'ok',
                'message' => 'Residence vacated successfully.',
                'closure_reason' => $closureReason,
            ];
        });
    }

    private function validateResidenceDate(mixed $rawDate): array
    {
        $dateValue = trim((string) $rawDate);

        try {
            $date = Carbon::createFromFormat('Y-m-d', $dateValue);
        } catch (Throwable $e) {
            $date = null;
        }

        if (!$date || $date->format('Y-m-d') !== $dateValue) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Valid effective date is required.'];
        }

        if ($date->isFuture()) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Future effective date is not allowed.'];
        }

        return ['status' => 'ok', 'date' => $dateValue];
    }

    private function findResidenceRoomForUpdate(string $unitId, string $roomNo): ?object
    {
        $latestMonth = DB::table('util_unit_room_snapshot')->max('month_cycle');

        return DB::table('util_unit_room_snapshot')
            ->where('month_cycle', $latestMonth)
            ->where('unit_id', $unitId)
            ->where('room_no', $roomNo)
            ->lockForUpdate()
            ->first();
    }

    private function isEligibleResidenceType(string $residenceType): bool
    {
        $type = strtoupper(trim($residenceType));

        return str_starts_with($type, 'HOUSE')
            || in_array($type, ['BACHELOR', 'HOSTEL', 'CONTAINER'], true);
    }

    private function isHouseResidence(string $residenceType): bool
    {
        return str_starts_with(strtoupper(trim($residenceType)), 'HOUSE');
    }

    private function determineOccupancyMode(string $companyId, string $residenceType): string
    {
        $hasFamily = DB::table('family_members')
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->exists();

        return $this->isHouseResidence($residenceType) && $hasFamily
            ? 'HOUSEHOLD'
            : 'INDIVIDUAL';
    }

    private function hasActiveHouseOccupant(string $unitId, string $roomNo, ?int $ignoreAssignmentId = null): bool
    {
        $query = DB::table('employee_residence_assignments')
            ->where('status', 'ACTIVE')
            ->where('unit_id', $unitId)
            ->where('room_no', $roomNo);

        if ($ignoreAssignmentId !== null) {
            $query->where('id', '<>', $ignoreAssignmentId);
        }

        return $query->exists();
    }

    private function insertResidenceAssignment(
        string $companyId,
        object $room,
        string $occupancyMode,
        string $startDate,
        string $startReason,
        string $remarks,
        string $createdBy
    ): int {
        return (int) DB::table('employee_residence_assignments')->insertGetId([
            'company_id' => $companyId,
            'residence_type' => (string) $room->residence_type,
            'category' => (string) ($room->category ?? ''),
            'unit_id' => (string) $room->unit_id,
            'block_floor' => $room->block_floor ?? null,
            'room_no' => (string) $room->room_no,
            'occupancy_mode' => $occupancyMode,
            'start_date' => $startDate,
            'end_date' => null,
            'status' => 'ACTIVE',
            'start_reason' => $startReason,
            'closure_reason' => null,
            'source_month_cycle' => null,
            'source_record_type' => 'USER_RESIDENCE_ACTION',
            'remarks' => $remarks === '' ? null : $remarks,
            'created_by' => $createdBy === '' ? null : $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function recordFamilyMovement(string $companyId, int $familyMemberId, array $payload): array
    {
        $companyId = trim($companyId);
        $movementType = strtoupper(trim((string) ($payload['movement_type'] ?? '')));
        $movementDate = trim((string) ($payload['movement_date'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));
        $createdBy = trim((string) ($payload['created_by'] ?? ''));

        if (!in_array($movementType, ['MOVE_OUT', 'RETURN_BACK'], true)) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Invalid family movement type.'];
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $movementDate);
        } catch (Throwable $e) {
            $date = null;
        }

        if (!$date || $date->format('Y-m-d') !== $movementDate) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Valid movement date is required.'];
        }

        if ($date->isFuture()) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Future movement date is not allowed.'];
        }

        if (mb_strlen($remarks) > 1000) {
            return ['_http' => 422, 'status' => 'error', 'error' => 'Remarks cannot exceed 1000 characters.'];
        }

        return DB::transaction(function () use ($companyId, $familyMemberId, $movementType, $movementDate, $remarks, $createdBy) {
            $member = DB::table('family_members')
                ->where('id', $familyMemberId)
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->first();

            if (!$member) {
                return ['_http' => 404, 'status' => 'error', 'error' => 'Family member not found for this employee.'];
            }

            $currentStatus = strtoupper(trim((string) $member->current_status));

            $requiredStatus = $movementType === 'MOVE_OUT' ? 'PRESENT' : 'MOVED_OUT';
            $newStatus = $movementType === 'MOVE_OUT' ? 'MOVED_OUT' : 'PRESENT';

            if ($currentStatus !== $requiredStatus) {
                return [
                    '_http' => 409,
                    'status' => 'error',
                    'error' => $movementType === 'MOVE_OUT'
                        ? 'Move Out is allowed only for a PRESENT member.'
                        : 'Move In is allowed only for a MOVED_OUT member.',
                ];
            }

            $latestMovement = DB::table('family_member_movements')
                ->where('family_member_id', $familyMemberId)
                ->orderByDesc('movement_date')
                ->orderByDesc('id')
                ->first();

            if ($latestMovement && $movementDate < $latestMovement->movement_date) {
                return [
                    '_http' => 422,
                    'status' => 'error',
                    'error' => 'Movement date cannot be earlier than the latest recorded movement.',
                ];
            }

            DB::table('family_member_movements')->insert([
                'family_member_id' => $familyMemberId,
                'movement_type' => $movementType,
                'movement_date' => $movementDate,
                'remarks' => $remarks === '' ? null : $remarks,
                'created_by' => $createdBy === '' ? null : $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('family_members')
                ->where('id', $familyMemberId)
                ->update([
                    'current_status' => $newStatus,
                    'updated_at' => now(),
                ]);

            return [
                'status' => 'ok',
                'message' => $movementType === 'MOVE_OUT'
                    ? $member->member_name . ' moved out successfully.'
                    : $member->member_name . ' moved in successfully.',
                'new_status' => $newStatus,
            ];
        });
    }
}
