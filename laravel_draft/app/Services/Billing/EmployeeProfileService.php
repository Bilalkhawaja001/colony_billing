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
            'current_house' => (string) ($employee->unit_id ?? $employee->room_no ?? ''),
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
                'current_status' => $status,
                'current_house' => (string) ($member['source_room_no'] ?? ''),
                'is_family_head' => false,
                'next_movement_type' => $status === 'PRESENT' ? 'MOVE_OUT' : ($status === 'MOVED_OUT' ? 'RETURN_BACK' : null),
                'next_action_label' => $status === 'PRESENT' ? 'Move Out' : ($status === 'MOVED_OUT' ? 'Return Back' : null),
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
                'unit_id' => (string) ($employee->unit_id ?? ''),
                'colony_type' => (string) ($employee->colony_type ?? ''),
                'block_floor' => (string) ($employee->block_floor ?? ''),
                'room_no' => (string) ($employee->room_no ?? ''),
                'shared_room' => (string) ($employee->shared_room ?? ''),
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
            'assets' => $assets,
        ];
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
                        : 'Return Back is allowed only for a MOVED_OUT member.',
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
                    : $member->member_name . ' returned back successfully.',
                'new_status' => $newStatus,
            ];
        });
    }
}
