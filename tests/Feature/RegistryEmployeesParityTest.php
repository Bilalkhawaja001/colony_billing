<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistryEmployeesParityTest extends TestCase
{
    private function authDataEntry(): void
    {
        $this->withSession(['user_id' => 5, 'role' => 'DATA_ENTRY', 'force_change_password' => 0]);
    }

    public function test_registry_upsert_get_preview_commit_promote(): void
    {
        $this->authDataEntry();

        DB::table('util_unit')->insert([
            'unit_id' => 'WA-20', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->postJson('/registry/employees/upsert', [
            'CompanyID' => 'R001', 'Name' => 'Bilal', 'CNIC_No.' => '123', 'Department' => 'Ops', 'Designation' => 'Mgr', 'Unit_ID' => 'WA-20',
        ])->assertOk()->assertJsonPath('CompanyID', 'R001');

        $this->getJson('/registry/employees/R001')->assertOk()->assertJsonPath('row.company_id', 'R001');

        $csv = "CompanyID,Name,CNIC_No.,Department,Designation,Unit_ID\nR002,Test,999,Ops,Eng,WA-20\n";
        $this->postJson('/registry/employees/import-preview', ['csv_text' => $csv])
            ->assertOk()
            ->assertJsonPath('accepted_rows', 1)
            ->assertJsonPath('valid_rows', 1)
            ->assertJsonPath('rejected_rows', 0);

        $this->postJson('/registry/employees/import-commit', ['csv_text' => $csv])
            ->assertOk()
            ->assertJsonPath('inserted_rows', 1)
            ->assertJsonPath('rejected_rows', 0);

        $this->postJson('/registry/employees/promote-to-master', ['upsert' => true])
            ->assertOk()->assertJsonPath('promoted', 2);

        $this->assertTrue(DB::table('employees_master')->where('company_id', 'R001')->exists());
        $this->assertTrue(DB::table('employees_master')->where('company_id', 'R002')->exists());
    }

    public function test_registry_import_partial_success_reject_reasons_and_preview_commit_counts_match(): void
    {
        $this->authDataEntry();

        DB::table('util_unit')->insert([
            'unit_id' => 'WA-21', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('employees_master')->insert([
            'company_id' => 'R010',
            'name' => 'Existing Master',
            'department' => 'Ops',
            'designation' => 'Mgr',
            'unit_id' => 'WA-21',
            'active' => 'Yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $csv = "CompanyID,Name,CNIC_No.,Department,Designation,Unit_ID\nR011,Valid One,123,Ops,Eng,WA-21\nR011,Duplicate In File,124,Ops,Eng,WA-21\nR010,Already Exists,125,Ops,Eng,WA-21\nR012,Bad Unit,126,Ops,Eng,NO-UNIT\n";

        $preview = $this->postJson('/registry/employees/import-preview', ['csv_text' => $csv])
            ->assertOk()
            ->assertJsonPath('total_rows', 4)
            ->assertJsonPath('valid_rows', 1)
            ->assertJsonPath('rejected_rows', 3);

        $commit = $this->postJson('/registry/employees/import-commit', ['csv_text' => $csv])
            ->assertOk()
            ->assertJsonPath('total_rows', 4)
            ->assertJsonPath('valid_rows', 1)
            ->assertJsonPath('inserted_rows', 1)
            ->assertJsonPath('updated_rows', 0)
            ->assertJsonPath('rejected_rows', 3);

        $errors = $commit->json('errors_preview');
        $this->assertSame('DUPLICATE_COMPANY_ID_IN_FILE', $errors[0]['error_code']);
        $this->assertSame('COMPANY_ID_ALREADY_EXISTS', $errors[1]['error_code']);
        $this->assertSame('INVALID_UNIT_ID', $errors[2]['error_code']);
        $this->assertSame($preview->json('valid_rows'), $commit->json('valid_rows'));
        $this->assertSame($preview->json('rejected_rows'), $commit->json('rejected_rows'));
        $this->assertTrue(DB::table('employees_registry')->where('company_id', 'R011')->exists());
    }
}
