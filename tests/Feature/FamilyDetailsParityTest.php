<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FamilyDetailsParityTest extends TestCase
{
    private function authDataEntry(): void
    {
        $this->withSession(['user_id' => 5, 'role' => 'DATA_ENTRY', 'force_change_password' => 0]);
    }

    public function test_family_context_and_upsert_and_list(): void
    {
        $this->authDataEntry();

        DB::table('employees_master')->insert([
            'company_id' => 'E300', 'name' => 'Ahsan', 'unit_id' => 'WA-12', 'colony_type' => 'Family Colony', 'block_floor' => 'A', 'room_no' => '12',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('util_unit_room_snapshot')->insert([
            'month_cycle' => '03-2026', 'unit_id' => 'WA-12', 'category' => 'Family A', 'room_no' => '12', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->getJson('/family/details/context?month_cycle=03-2026&company_id=E300')
            ->assertOk()->assertJsonPath('row.category', 'Family A');

        $this->postJson('/family/details/upsert', [
            'month_cycle' => '03-2026',
            'company_id' => 'E300',
            'employee_name' => 'Ahsan',
            'unit_id' => 'WA-12',
            'spouse_name' => 'Sara',
            'children' => [[
                'child_name' => 'Ali',
                'age' => 10,
                'school_going' => true,
                'school_name' => 'APS',
                'class_name' => '5',
                'van_using_child' => true,
            ]],
        ])->assertOk()->assertJsonPath('children_saved', 1);

        $this->getJson('/family/details?month_cycle=03-2026&company_id=E300')
            ->assertOk()
            ->assertJsonPath('rows.0.company_id', 'E300')
            ->assertJsonPath('rows.0.children.0.child_name', 'Ali');
    }
}
