<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase3FunctionalGapClosureTest extends TestCase
{
    private function asAdmin(): void
    {
        $this->withSession([
            'user_id' => 9901,
            'actor_user_id' => 9901,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function asViewer(): void
    {
        $this->withSession([
            'user_id' => 9902,
            'actor_user_id' => 9902,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    public function test_phase3_rooms_occupancy_inputs_water_functional_gap_closure_proof(): void
    {
        $this->asAdmin();
        $month = '03-2027';

        // Ensure month exists for guard-aware writes
        $this->postJson('/month/open', ['month_cycle' => $month])->assertOk();

        // --- Rooms create/update/import-like batch + validation + reload ---
        $this->postJson('/rooms/upsert', [
            'month_cycle' => $month,
            'unit_id' => 'U-701',
            'category' => 'Family A',
            'room_no' => 'R-1',
            'block_floor' => 'B1',
        ])->assertOk()->assertJsonPath('status', 'ok');

        // update existing row
        $this->postJson('/rooms/upsert', [
            'month_cycle' => $month,
            'unit_id' => 'U-701',
            'category' => 'Family A',
            'room_no' => 'R-1',
            'block_floor' => 'B2',
        ])->assertOk()->assertJsonPath('status', 'ok');

        // import-like batch (simulate CSV rows through existing endpoint)
        foreach ([
            ['month_cycle' => $month, 'unit_id' => 'U-701', 'category' => 'Family A', 'room_no' => 'R-2', 'block_floor' => 'B2'],
            ['month_cycle' => $month, 'unit_id' => 'U-702', 'category' => 'Hostel', 'room_no' => 'H-1', 'block_floor' => 'H1'],
        ] as $row) {
            $this->postJson('/rooms/upsert', $row)->assertOk()->assertJsonPath('status', 'ok');
        }

        // validation error case
        $this->postJson('/rooms/upsert', [
            'month_cycle' => $month,
            'unit_id' => 'U-703',
            'category' => 'INVALID_CAT',
            'room_no' => 'X-1',
        ])->assertStatus(400)->assertJsonPath('status', 'error');

        // reload/list persistence
        $rooms = $this->getJson('/rooms?month_cycle='.$month)->assertOk()->json('rows');
        $this->assertGreaterThanOrEqual(3, count((array) $rooms));

        // --- Occupancy create/update/import-like batch + validation + reload ---
        DB::table('employees_master')->updateOrInsert(
            ['company_id' => 'E701'],
            ['name' => 'Emp 701', 'active' => 'Yes', 'department' => 'Ops', 'designation' => 'Tech', 'updated_at' => now(), 'created_at' => now()]
        );
        DB::table('employees_master')->updateOrInsert(
            ['company_id' => 'E702'],
            ['name' => 'Emp 702', 'active' => 'Yes', 'department' => 'Ops', 'designation' => 'Tech', 'updated_at' => now(), 'created_at' => now()]
        );

        $this->postJson('/occupancy/upsert', [
            'month_cycle' => $month,
            'category' => 'Family A',
            'room_no' => 'R-1',
            'unit_id' => 'U-701',
            'employee_id' => 'E701',
            'block_floor' => 'B2',
            'active_days' => 30,
        ])->assertOk()->assertJsonPath('status', 'ok');

        // update path
        $this->postJson('/occupancy/upsert', [
            'month_cycle' => $month,
            'category' => 'Family A',
            'room_no' => 'R-1',
            'unit_id' => 'U-701',
            'employee_id' => 'E701',
            'block_floor' => 'B3',
            'active_days' => 29,
        ])->assertOk()->assertJsonPath('status', 'ok');

        // import-like batch row 2
        $this->postJson('/occupancy/upsert', [
            'month_cycle' => $month,
            'category' => 'Hostel',
            'room_no' => 'H-1',
            'unit_id' => 'U-702',
            'employee_id' => 'E702',
            'block_floor' => 'H1',
            'active_days' => 30,
        ])->assertOk()->assertJsonPath('status', 'ok');

        // validation error
        $this->postJson('/occupancy/upsert', [
            'month_cycle' => $month,
            'unit_id' => 'U-701',
            'employee_id' => 'E701',
        ])->assertStatus(400)->assertJsonPath('status', 'error');

        // reload/list persistence
        $occ = $this->getJson('/occupancy?month_cycle='.$month)->assertOk()->json('rows');
        $this->assertGreaterThanOrEqual(2, count((array) $occ));

        // --- Inputs Mapping/Readings/RO and Water operational paths ---
        $this->postJson('/api/rooms/cascade', ['month_cycle' => $month, 'unit_id' => 'U-999'])
            ->assertOk()->assertJsonPath('status', 'ok');

        $this->postJson('/meter-reading/upsert', [
            'meter_id' => 'M-701',
            'unit_id' => 'U-701',
            'reading_date' => '2027-03-01',
            'reading_value' => 123,
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->getJson('/meter-reading/latest/U-701')->assertOk()->assertJsonPath('status', 'ok');
        $this->getJson('/api/water/allocation-preview?month_cycle='.$month)->assertOk();

        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => $month,
            'rows' => [
                ['water_zone' => 'FAMILY_METER', 'raw_liters' => 500, 'common_use_liters' => 50],
                ['water_zone' => 'BACHELOR_METER', 'raw_liters' => 200, 'common_use_liters' => 20],
            ],
        ])->assertOk()->assertJsonPath('status', 'ok');

        // --- Role/access behavior ---
        $this->asViewer();
        $this->postJson('/rooms/upsert', [
            'month_cycle' => $month,
            'unit_id' => 'U-705',
            'category' => 'Family A',
            'room_no' => 'R-5',
        ])->assertStatus(403);

        $this->postJson('/occupancy/upsert', [
            'month_cycle' => $month,
            'category' => 'Family A',
            'room_no' => 'R-1',
            'unit_id' => 'U-701',
            'employee_id' => 'E701',
            'active_days' => 30,
        ])->assertStatus(403);

        $this->postJson('/api/water/zone-adjustments', [
            'month_cycle' => $month,
            'rows' => [['water_zone' => 'FAMILY_METER', 'raw_liters' => 1, 'common_use_liters' => 1]],
        ])->assertStatus(403);
    }
}
