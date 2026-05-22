<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

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

        $employeeActive = strtoupper(trim((string) ($employee->active ?? ''))) === 'YES';

        $familyRows = [[
            'member_name' => (string) $employee->name,
            'relation' => 'Family Head / Employee',
            'age' => null,
            'school_going' => null,
            'current_status' => $employeeActive ? 'ACTIVE' : 'INACTIVE',
            'current_house' => (string) ($employee->unit_id ?? $employee->room_no ?? ''),
            'is_family_head' => true,
        ]];

        foreach ($linkedMembers as $member) {
            $familyRows[] = [
                'member_name' => (string) ($member['member_name'] ?? ''),
                'relation' => (string) ($member['relation'] ?? ''),
                'age' => $member['age'] ?? null,
                'school_going' => $member['school_going'] ?? null,
                'current_status' => (string) ($member['current_status'] ?? ''),
                'current_house' => (string) ($member['source_room_no'] ?? ''),
                'is_family_head' => false,
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
            'assets' => $assets,
        ];
    }
}
