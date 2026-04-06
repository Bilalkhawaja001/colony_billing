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
            ->assertOk()->assertJsonPath('accepted_rows', 1);

        $this->postJson('/registry/employees/import-commit', ['csv_text' => $csv])
            ->assertOk()->assertJsonPath('inserted', 1);

        $this->postJson('/registry/employees/promote-to-master', ['upsert' => true])
            ->assertOk()->assertJsonPath('promoted', 2);

        $this->assertTrue(DB::table('employees_master')->where('company_id', 'R001')->exists());
        $this->assertTrue(DB::table('employees_master')->where('company_id', 'R002')->exists());
    }
}
