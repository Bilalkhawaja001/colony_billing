<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterCrudParityTest extends TestCase
{
    private function authAsDataEntry(): void
    {
        $this->withSession([
            'user_id' => 7,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);
    }

    public function test_units_list_upsert_delete_flow(): void
    {
        $this->authAsDataEntry();

        $this->postJson('/units/upsert', [
            'unit_id' => 'WA-01',
            'colony_type' => 'Family Colony A',
            'block_name' => 'A-1',
            'room_no' => 'R-10',
        ])->assertOk()->assertJsonPath('unit_id', 'WA-01');

        $this->getJson('/units?q=WA')->assertOk()->assertJsonPath('rows.0.unit_id', 'WA-01');

        $this->deleteJson('/units/WA-01')->assertOk()->assertJsonPath('policy', 'soft-delete');
        $this->assertSame(0, DB::table('util_unit')->where('unit_id', 'WA-01')->value('is_active'));
    }

    public function test_rooms_list_upsert_delete_flow(): void
    {
        $this->authAsDataEntry();

        DB::table('util_unit')->insert([
            'unit_id' => 'WA-02',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/rooms/upsert', [
            'month_cycle' => '03-2026',
            'unit_id' => 'WA-02',
            'category' => 'Family A',
            'block_floor' => 'A',
            'room_no' => '101',
        ])->assertOk()->assertJsonPath('status', 'ok');

        $list = $this->getJson('/rooms?month_cycle=03-2026&unit_id=WA-02');
        $list->assertOk()->assertJsonPath('rows.0.room_no', '101');
        $rowId = (int) $list->json('rows.0.id');

        $this->deleteJson('/rooms/'.$rowId)->assertOk()->assertJsonPath('id', $rowId);
        $this->assertNull(DB::table('util_unit_room_snapshot')->where('id', $rowId)->first());
    }

    public function test_occupancy_context_list_upsert_delete_flow(): void
    {
        $this->authAsDataEntry();

        DB::table('employees_master')->insert([
            'company_id' => 'E001',
            'name' => 'Ali',
            'department' => 'Ops',
            'designation' => 'Engineer',
            'unit_id' => 'WA-03',
            'active' => 'Yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('util_unit_room_snapshot')->insert([
            'month_cycle' => '03-2026',
            'unit_id' => 'WA-03',
            'category' => 'Family A',
            'block_floor' => 'A',
            'room_no' => '102',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/occupancy/context?month_cycle=03-2026&company_id=E001')
            ->assertOk()
            ->assertJsonPath('row.category', 'Family A')
            ->assertJsonPath('row.unit_id', 'WA-03');

        $this->postJson('/occupancy/upsert', [
            'month_cycle' => '03-2026',
            'category' => 'Family A',
            'block_floor' => 'A',
            'room_no' => '102',
            'unit_id' => 'WA-03',
            'employee_id' => 'E001',
            'active_days' => 29,
        ])->assertOk()->assertJsonPath('employee_name', 'Ali');

        $list = $this->getJson('/occupancy?month_cycle=03-2026&unit_id=WA-03');
        $list->assertOk()->assertJsonPath('rows.0.employee_id', 'E001');
        $occId = (int) $list->json('rows.0.id');

        $this->deleteJson('/occupancy/'.$occId)->assertOk()->assertJsonPath('id', $occId);
        $this->assertNull(DB::table('util_occupancy_monthly')->where('id', $occId)->first());
    }

    public function test_occupancy_autofill_populates_from_active_employees(): void
    {
        $this->authAsDataEntry();

        DB::table('employees_master')->insert([
            [
                'company_id' => 'E101',
                'name' => 'Sara',
                'unit_id' => 'WA-10',
                'active' => 'Yes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 'E102',
                'name' => 'Hina',
                'unit_id' => 'WA-99',
                'active' => 'Yes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('util_unit_room_snapshot')->insert([
            [
                'month_cycle' => '03-2026',
                'unit_id' => 'WA-10',
                'category' => 'Family A',
                'block_floor' => 'A',
                'room_no' => '201',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'month_cycle' => '03-2026',
                'unit_id' => 'WB-01',
                'category' => 'Family B',
                'block_floor' => 'B',
                'room_no' => '301',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->postJson('/api/occupancy/autofill?month_cycle=03-2026')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('rows', 2);

        $this->assertSame(2, DB::table('util_occupancy_monthly')->where('month_cycle', '03-2026')->count());
    }
}
