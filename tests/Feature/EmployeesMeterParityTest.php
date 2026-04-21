<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeesMeterParityTest extends TestCase
{
    private function authAsDataEntry(): void
    {
        $this->withSession([
            'user_id' => 8,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);
    }

    public function test_employees_endpoints_list_search_get_meta_add_upsert_patch_delete_import(): void
    {
        $this->authAsDataEntry();

        $this->postJson('/employees/add', [
            'company_id' => 'E900',
            'name' => 'Zara',
            'cnic_no' => '11111-1111111-1',
            'department' => 'HR',
            'designation' => 'Officer',
            'unit_id' => 'UA-1',
        ])->assertOk()->assertJsonPath('company_id', 'E900');

        $this->postJson('/employees/add', [
            'company_id' => 'E900',
            'name' => 'Duplicate',
            'cnic_no' => '22222-2222222-2',
            'department' => 'HR',
            'designation' => 'Officer',
            'unit_id' => 'UA-1',
        ])->assertStatus(409);

        $this->postJson('/employees/upsert', [
            'company_id' => 'E901',
            'name' => 'Bilal',
            'department' => 'IT',
            'designation' => 'Lead',
        ])->assertOk()->assertJsonPath('company_id', 'E901');

        $this->getJson('/employees?q=E9')
            ->assertOk()
            ->assertJsonPath('rows.0.company_id', 'E900');

        $this->getJson('/employees/search?q=Bil')
            ->assertOk()
            ->assertJsonPath('rows.0.company_id', 'E901');

        $this->getJson('/employees/meta/departments')
            ->assertOk()
            ->assertJsonFragment(['HR'])
            ->assertJsonFragment(['IT']);

        $this->getJson('/employees/E900')
            ->assertOk()
            ->assertJsonPath('row.name', 'Zara');

        $this->patchJson('/employees/E900', [
            'designation' => 'Sr Officer',
            'active' => 'Yes',
        ])->assertStatus(405);

        $this->assertSame('Officer', DB::table('employees_master')->where('company_id', 'E900')->value('designation'));

        $csv = "company_id,name,department,designation\nE902,Ali,Ops,Engineer\nE903,Sara,Ops,Manager";
        $this->postJson('/employees/import', ['csv_text' => $csv])
            ->assertOk()
            ->assertJsonPath('inserted_rows', 2)
            ->assertJsonPath('rejected_rows', 0);

        $this->deleteJson('/employees/E901')
            ->assertStatus(405);

        $this->assertSame('Yes', DB::table('employees_master')->where('company_id', 'E901')->value('active'));
    }

    public function test_employees_search_validation_error(): void
    {
        $this->authAsDataEntry();

        $this->getJson('/employees/search')
            ->assertStatus(400)
            ->assertJsonPath('status', 'error');
    }

    public function test_meter_unit_and_meter_reading_latest_and_upsert(): void
    {
        $this->authAsDataEntry();

        $this->postJson('/meter-unit/upsert', [
            'meter_id' => 'M-1',
            'unit_id' => 'U-1',
            'meter_type' => 'water',
        ])->assertOk()->assertJsonPath('meter_id', 'M-1');

        $this->getJson('/meter-unit?q=M-1')
            ->assertOk()
            ->assertJsonPath('rows.0.unit_id', 'U-1');

        $this->postJson('/meter-reading/upsert', [
            'meter_id' => 'M-1',
            'unit_id' => 'U-1',
            'reading_date' => '2026-03-01',
            'reading_value' => 41.5,
        ])->assertOk();

        $this->postJson('/meter-reading/upsert', [
            'meter_id' => 'M-1',
            'unit_id' => 'U-1',
            'reading_date' => '2026-03-10',
            'reading_value' => 44.2,
        ])->assertOk();

        $this->getJson('/meter-reading/latest/U-1')
            ->assertOk()
            ->assertJsonPath('row.reading_value', 44.2)
            ->assertJsonPath('row.reading_date', '2026-03-10');
    }

    public function test_employee_bulk_import_partial_success_rejects_duplicates_and_keeps_valid_rows(): void
    {
        $this->authAsDataEntry();

        DB::table('employees_master')->insert([
            'company_id' => 'E950',
            'name' => 'Existing',
            'department' => 'Ops',
            'designation' => 'Lead',
            'active' => 'Yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $csv = "company_id,name,department,designation\nE951,New One,Ops,Engineer\nE951,Dupe In File,Ops,Engineer\nE950,Already Exists,Ops,Manager\n,Missing Id,Ops,Staff";

        $response = $this->postJson('/employees/import', ['csv_text' => $csv])
            ->assertOk()
            ->assertJsonPath('total_rows', 4)
            ->assertJsonPath('valid_rows', 1)
            ->assertJsonPath('inserted_rows', 1)
            ->assertJsonPath('updated_rows', 0)
            ->assertJsonPath('rejected_rows', 3);

        $errors = $response->json('errors_preview');
        $this->assertSame('DUPLICATE_COMPANY_ID_IN_FILE', $errors[0]['error_code']);
        $this->assertSame('COMPANY_ID_ALREADY_EXISTS', $errors[1]['error_code']);
        $this->assertSame('MISSING_REQUIRED', $errors[2]['error_code']);
        $this->assertTrue(DB::table('employees_master')->where('company_id', 'E951')->exists());
    }

    public function test_active_days_import_partial_success_rejects_unknown_and_invalid_rows(): void
    {
        $this->authAsDataEntry();

        DB::table('employees_master')->insert([
            [
                'company_id' => 'AD100',
                'name' => 'Worker One',
                'department' => 'Ops',
                'designation' => 'Tech',
                'active' => 'Yes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => 'AD200',
                'name' => 'Worker Two',
                'department' => 'Ops',
                'designation' => 'Tech',
                'active' => 'Yes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $csv = "company_id,active_days\nAD100,30\nZZ999,12\nAD200,abc\nAD200,32";

        $preview = $this->postJson('/imports/active-days/import', ['month_cycle' => '04-2026', 'csv_text' => $csv, 'commit' => false])
            ->assertOk()
            ->assertJsonPath('mode', 'preview')
            ->assertJsonPath('total_rows', 4)
            ->assertJsonPath('valid_rows', 1)
            ->assertJsonPath('rejected_rows', 3);

        $commit = $this->postJson('/imports/active-days/import', ['month_cycle' => '04-2026', 'csv_text' => $csv, 'commit' => true])
            ->assertOk()
            ->assertJsonPath('mode', 'commit')
            ->assertJsonPath('valid_rows', 1)
            ->assertJsonPath('inserted_rows', 1)
            ->assertJsonPath('updated_rows', 0)
            ->assertJsonPath('rejected_rows', 3);

        $this->assertSame(1, DB::table('hr_input')->where('month_cycle', '04-2026')->count());
        $this->assertEquals(30.0, (float) DB::table('hr_input')->where('month_cycle', '04-2026')->where('company_id', 'AD100')->value('active_days'));
        $this->assertSame($preview->json('valid_rows'), $commit->json('valid_rows'));
        $this->assertSame($preview->json('rejected_rows'), $commit->json('rejected_rows'));
    }

    public function test_rooms_cascade_deletes_rooms_and_occupancy_for_unit_month(): void
    {
        $this->authAsDataEntry();

        DB::table('util_unit_room_snapshot')->insert([
            'month_cycle' => '03-2026',
            'unit_id' => 'UC-1',
            'category' => 'Family A',
            'room_no' => '101',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('util_occupancy_monthly')->insert([
            'month_cycle' => '03-2026',
            'category' => 'Family A',
            'room_no' => '101',
            'unit_id' => 'UC-1',
            'employee_id' => 'E-CAS',
            'active_days' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/rooms/cascade', [
            'month_cycle' => '03-2026',
            'unit_id' => 'UC-1',
        ])
            ->assertOk()
            ->assertJsonPath('deleted.rooms_rows', 1)
            ->assertJsonPath('deleted.occupancy_rows', 1);

        $this->assertSame(0, DB::table('util_unit_room_snapshot')->where('month_cycle', '03-2026')->where('unit_id', 'UC-1')->count());
        $this->assertSame(0, DB::table('util_occupancy_monthly')->where('month_cycle', '03-2026')->where('unit_id', 'UC-1')->count());
    }
}
