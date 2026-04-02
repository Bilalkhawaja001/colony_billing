<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransportChildFoundationTest extends TestCase
{
    private function authAsDataEntry(): void
    {
        $this->withSession([
            'user_id' => 7,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);
    }

    private function seedEmployee(string $companyId = 'E1001', string $name = 'Bilal', string $roomNo = 'R-01'): void
    {
        DB::table('employees_master')->updateOrInsert(
            ['company_id' => $companyId],
            [
                'company_id' => $companyId,
                'name' => $name,
                'department' => 'Admin',
                'designation' => 'Officer',
                'cnic_no' => '12345-1234567-1',
                'unit_id' => 'U-001',
                'room_no' => $roomNo,
                'active' => 'Yes',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedMonth(string $monthCycle, string $state = 'OPEN'): void
    {
        DB::table('util_month_cycle')->updateOrInsert(
            ['month_cycle' => $monthCycle],
            ['state' => $state, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function seedVehicle(): int
    {
        return DB::table('transport_vehicles')->insertGetId([
            'vehicle_code' => 'VAN-01',
            'vehicle_name' => 'School Van',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_family_save_creates_child_profile_and_reload_returns_transport_fields(): void
    {
        $this->authAsDataEntry();
        $this->seedEmployee();

        $this->postJson('/family/details/upsert', [
            'month_cycle' => '04-2026',
            'company_id' => 'E1001',
            'spouse_name' => 'Test',
            'van_using_adults' => 0,
            'remarks' => 'family save',
            'children' => [[
                'child_name' => 'Ali',
                'age' => 10,
                'school_going' => 1,
                'school_name' => 'Beacon',
                'class_name' => '5',
                'van_using_child' => 1,
                'transport_join_date' => '2026-04-01',
                'transport_leave_date' => '2026-04-30',
                'default_route_label' => 'Route A',
                'notes' => 'Morning shift',
            ]],
        ])->assertOk()->assertJsonPath('children_saved', 1);

        $this->assertDatabaseHas('family_child_profiles', [
            'company_id' => 'E1001',
            'child_name' => 'Ali',
            'school_name' => 'Beacon',
            'class_grade' => '5',
            'default_route_label' => 'Route A',
        ]);

        $response = $this->getJson('/family/details?month_cycle=04-2026&company_id=E1001');
        $response->assertOk()
            ->assertJsonPath('rows.0.children.0.child_name', 'Ali')
            ->assertJsonPath('rows.0.children.0.transport_join_date', '2026-04-01')
            ->assertJsonPath('rows.0.children.0.transport_leave_date', '2026-04-30')
            ->assertJsonPath('rows.0.children.0.default_route_label', 'Route A');
    }

    public function test_family_save_without_child_profile_id_uses_safe_matching_and_does_not_duplicate_profile(): void
    {
        $this->authAsDataEntry();
        $this->seedEmployee();

        $payload = [
            'month_cycle' => '04-2026',
            'company_id' => 'E1001',
            'children' => [[
                'child_name' => 'Ali',
                'age' => 10,
                'school_going' => 1,
                'school_name' => 'Beacon',
                'class_name' => '5',
                'van_using_child' => 1,
                'transport_join_date' => '2026-04-01',
                'transport_leave_date' => '2026-04-30',
                'default_route_label' => 'Route A',
                'notes' => 'Morning shift',
            ]],
        ];

        $this->postJson('/family/details/upsert', $payload)->assertOk();
        $this->postJson('/family/details/upsert', $payload)->assertOk();

        $this->assertSame(1, DB::table('family_child_profiles')->where('company_id', 'E1001')->where('child_name', 'Ali')->count());
    }

    public function test_transport_child_month_usage_create_update_and_listing_derives_family_context(): void
    {
        $this->authAsDataEntry();
        $this->seedEmployee('E2001', 'Father One', 'R-09');
        $this->seedMonth('05-2026', 'OPEN');
        $vehicleId = $this->seedVehicle();

        $profileId = DB::table('family_child_profiles')->insertGetId([
            'company_id' => 'E2001',
            'child_name' => 'Sara',
            'school_name' => 'LGS',
            'class_grade' => '3',
            'school_going' => 1,
            'van_using' => 1,
            'transport_join_date' => '2026-05-01',
            'default_route_label' => 'Route B',
            'is_active' => 1,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/transport/child-month-usage/upsert', [
            'month_cycle' => '05-2026',
            'child_profile_id' => $profileId,
            'usage_status' => 'active',
            'usage_from_date' => '2026-05-01',
            'usage_to_date' => '2026-05-31',
            'vehicle_id' => $vehicleId,
            'route_label' => 'Route B',
            'charge_amount' => 2500,
            'remarks' => 'Monthly charge',
        ])->assertOk()->assertJsonPath('message', 'Child transport month usage saved successfully.');

        $this->postJson('/api/transport/child-month-usage/upsert', [
            'month_cycle' => '05-2026',
            'child_profile_id' => $profileId,
            'usage_status' => 'active',
            'usage_from_date' => '2026-05-05',
            'usage_to_date' => '2026-05-31',
            'vehicle_id' => $vehicleId,
            'route_label' => 'Route B2',
            'charge_amount' => 2700,
            'remarks' => 'Updated monthly charge',
        ])->assertOk()->assertJsonPath('message', 'Child transport month usage updated successfully.');

        $this->assertSame(1, DB::table('transport_child_month_usage')->where('month_cycle', '05-2026')->where('child_profile_id', $profileId)->count());

        $response = $this->getJson('/api/transport/child-month-usage?month_cycle=05-2026');
        $response->assertOk()
            ->assertJsonPath('rows.0.company_id', 'E2001')
            ->assertJsonPath('rows.0.father_name', 'Father One')
            ->assertJsonPath('rows.0.room_no', 'R-09')
            ->assertJsonPath('rows.0.route_label', 'Route B2')
            ->assertJsonPath('rows.0.charge_amount', 2700);
    }

    public function test_count_fields_are_not_used_as_billing_truth_dependency(): void
    {
        $this->authAsDataEntry();
        $this->seedEmployee('E3001', 'Parent', 'R-10');

        $this->postJson('/family/details/upsert', [
            'month_cycle' => '06-2026',
            'company_id' => 'E3001',
            'children' => [[
                'child_name' => 'Ayan',
                'age' => 9,
                'school_going' => 1,
                'school_name' => 'APS',
                'class_name' => '4',
                'van_using_child' => 0,
                'transport_join_date' => '',
                'transport_leave_date' => '',
                'default_route_label' => '',
                'notes' => '',
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('family_details', [
            'company_id' => 'E3001',
            'month_cycle' => '06-2026',
            'children_count' => 1,
            'school_going_children' => 1,
            'van_using_children' => 0,
        ]);

        $this->assertDatabaseHas('family_child_profiles', [
            'company_id' => 'E3001',
            'child_name' => 'Ayan',
            'van_using' => 0,
        ]);
    }
}
