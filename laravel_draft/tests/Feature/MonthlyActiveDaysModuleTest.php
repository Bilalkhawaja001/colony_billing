<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonthlyActiveDaysModuleTest extends TestCase
{
    private function authAsBillingAdmin(): void
    {
        $this->withSession([
            'user_id' => 1,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function seedEmployee(string $companyId, string $name = 'Employee'): void
    {
        DB::table('employees_master')->updateOrInsert(
            ['company_id' => $companyId],
            ['name' => $name, 'active' => 'Yes', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function test_page_open_and_template_download_work(): void
    {
        $this->authAsBillingAdmin();

        $this->get('/active-days-monthly')
            ->assertOk()
            ->assertSee('Monthly Active Days Import');

        $this->get('/active-days-monthly/template')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertSee('company_id,active_days,remarks', false);
    }

    public function test_upload_valid_file_and_commit_import(): void
    {
        $this->authAsBillingAdmin();
        $this->seedEmployee('E1001', 'Ali');

        $file = UploadedFile::fake()->createWithContent('active-days.csv', "company_id,active_days,remarks\n,,\nE1001,28,ok\n");

        $preview = $this->post('/active-days-monthly/preview', [
            'billing_month_date' => '2026-02',
            'upload_file' => $file,
        ]);

        $preview->assertOk()
            ->assertJsonPath('summary.valid_rows', 1)
            ->assertJsonPath('summary.skipped_rows', 1)
            ->assertJsonPath('invalid_rows', []);

        $this->postJson('/active-days-monthly/import', [
            'billing_month_date' => '2026-02-01',
            'preview_token' => $preview->json('preview_token'),
            'replace_existing' => false,
        ])->assertOk()
            ->assertJsonPath('summary.inserted', 1)
            ->assertJsonPath('summary.updated', 0);

        $this->assertSame(28.0, (float) DB::table('electric_active_days_monthly')->where('company_id', 'E1001')->value('active_days'));
    }

    public function test_reject_invalid_active_days_unknown_company_and_duplicates(): void
    {
        $this->authAsBillingAdmin();
        $this->seedEmployee('E2001', 'Bilal');

        $file = UploadedFile::fake()->createWithContent('invalid.csv', implode("\n", [
            'company_id,active_days,remarks',
            'E2001,40,too high',
            'E9999,4,unknown',
            'E2001,5,duplicate',
        ]));

        $this->post('/active-days-monthly/preview', [
            'billing_month_date' => '2026-04',
            'upload_file' => $file,
        ])->assertOk()
            ->assertJsonPath('summary.valid_rows', 0)
            ->assertJsonPath('summary.invalid_rows', 3)
            ->assertJsonPath('invalid_rows.0.errors.0', 'active_days cannot exceed 30 for selected billing month')
            ->assertJsonPath('invalid_rows.1.errors.0', 'company_id does not exist in employees master')
            ->assertJsonPath('invalid_rows.2.errors.0', 'duplicate company_id in upload');
    }

    public function test_replace_existing_month_data(): void
    {
        $this->authAsBillingAdmin();
        $this->seedEmployee('E3001', 'Sara');
        $this->seedEmployee('E3002', 'Zoya');

        DB::table('electric_active_days_monthly')->insert([
            'billing_month_date' => '2026-03-01',
            'company_id' => 'E3001',
            'active_days' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $file = UploadedFile::fake()->createWithContent('replace.csv', "company_id,active_days,remarks\nE3002,20,replaced\n");

        $preview = $this->post('/active-days-monthly/preview', [
            'billing_month_date' => '2026-03',
            'replace_existing' => 1,
            'upload_file' => $file,
        ])->assertOk();

        $this->postJson('/active-days-monthly/import', [
            'billing_month_date' => '2026-03-01',
            'preview_token' => $preview->json('preview_token'),
            'replace_existing' => true,
        ])->assertOk()
            ->assertJsonPath('summary.inserted', 1)
            ->assertJsonPath('summary.updated', 0);

        $this->assertFalse(DB::table('electric_active_days_monthly')->where('billing_month_date', '2026-03-01')->where('company_id', 'E3001')->exists());
        $this->assertTrue(DB::table('electric_active_days_monthly')->where('billing_month_date', '2026-03-01')->where('company_id', 'E3002')->exists());
    }

    public function test_billing_run_reads_imported_active_days_for_room_path(): void
    {
        $this->authAsBillingAdmin();
        $this->seedEmployee('E4001', 'Imported Emp');

        DB::table('electric_active_days_monthly')->insert([
            'billing_month_date' => '2026-03-01',
            'company_id' => 'E4001',
            'active_days' => 15,
            'remarks' => 'monthly import',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/electric-v1/input/allowance/upsert', [
            'rows' => [[
                'unit_id' => 'U-AD-1',
                'free_electric' => 30,
                'unit_name' => 'Unit AD',
                'residence_type' => 'ROOM',
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/readings/upsert', [
            'rows' => [[
                'cycle_start_date' => '2026-04-10',
                'cycle_end_date' => '2026-04-20',
                'unit_id' => 'U-AD-1',
                'previous_reading' => 100,
                'current_reading' => 130,
                'reading_status' => 'NORMAL',
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/attendance/upsert', [
            'rows' => [[
                'cycle_start_date' => '2026-04-10',
                'cycle_end_date' => '2026-04-20',
                'company_id' => 'E4001',
                'attendance_days' => 1,
            ]],
        ])->assertOk();

        $this->postJson('/api/electric-v1/input/occupancy/upsert', [
            'rows' => [[
                'company_id' => 'E4001',
                'unit_id' => 'U-AD-1',
                'room_id' => 'R-1',
                'from_date' => '2026-04-10',
                'to_date' => '2026-04-20',
            ]],
        ])->assertOk();

        $run = $this->postJson('/api/electric-v1/run', [
            'billing_month_date' => '2026-03-01',
            'cycle_start' => '2026-04-10',
            'cycle_end' => '2026-04-20',
            'flat_rate' => 2,
        ])->assertOk()->json('data');

        $row = DB::table('electric_v1_output_employee_unit_drilldown')->where('run_id', $run['run_id'])->first();
        $this->assertNotNull($row);
        $this->assertSame(15.0, (float) $row->employee_attendance_in_unit);
        $this->assertSame(14.5161, round((float) $row->free_allowance_units, 4));
        $this->assertSame(15.4839, round((float) $row->net_units_before_adj, 4));
    }
}
