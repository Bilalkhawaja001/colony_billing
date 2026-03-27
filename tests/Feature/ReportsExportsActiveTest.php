<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsExportsActiveTest extends TestCase
{
    public function test_unauthenticated_blocked(): void
    {
        $this->getJson('/reports/monthly-summary?month_cycle=03-2026')->assertStatus(401);
        $this->get('/export/excel/reconciliation?month_cycle=03-2026')->assertStatus(302);
        $this->get('/export/excel/monthly-summary?month_cycle=03-2026')->assertStatus(302);
        $this->get('/export/pdf/monthly-summary?month_cycle=03-2026')->assertStatus(302);
    }

    public function test_unauthorized_role_blocked(): void
    {
        $this->withSession([
            'user_id' => 301,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);

        $this->getJson('/reports/monthly-summary?month_cycle=03-2026')->assertStatus(403);
        $this->getJson('/reports/reconciliation?month_cycle=03-2026')->assertStatus(403);
        $this->get('/ui/elec-summary')->assertStatus(403);
    }

    public function test_monthly_summary_response_schema(): void
    {
        $this->withSession([
            'user_id' => 302,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 11, 'run_status' => 'LOCKED']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['utility_type' => 'ELEC', 'total_amount' => 700.00, 'total_qty' => 10.0],
        ]);

        $res = $this->getJson('/reports/monthly-summary?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure([
            'status',
            'month_cycle',
            'billing_run_id',
            'rows',
            'summary' => ['utility_count', 'grand_total_amount'],
        ]);
    }

    public function test_recovery_report_response_schema(): void
    {
        $this->withSession([
            'user_id' => 303,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 15, 'run_status' => 'LOCKED']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) ['employee_id' => 'E-1', 'billed_amount' => 500.00, 'recovered_amount' => 0.00, 'outstanding_amount' => 500.00],
        ]);

        $res = $this->getJson('/reports/recovery?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure([
            'status',
            'month_cycle',
            'billing_run_id',
            'rows',
            'summary' => ['billed_total', 'recovered_total', 'outstanding_total'],
        ]);
    }

    public function test_employee_bill_summary_response_schema(): void
    {
        $this->withSession([
            'user_id' => 304,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 17, 'run_status' => 'APPROVED']);
        DB::shouldReceive('select')->once()->andReturn([
            (object) [
                'employee_id' => 'E-1',
                'employee_name' => 'John',
                'department' => 'Ops',
                'has_family' => 1,
                'electric_bill' => 500.00,
                'water_bill' => 120.00,
                'school_van_bill' => 0.00,
                'total_bill' => 620.00,
            ],
        ]);

        $res = $this->getJson('/reports/employee-bill-summary?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure([
            'status',
            'month_cycle',
            'billing_run_id',
            'rows',
            'summary' => ['employee_count', 'total_bill_amount'],
        ]);
    }

    public function test_elec_summary_response_schema(): void
    {
        $this->withSession([
            'user_id' => 305,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('select')->twice()->andReturn(
            [(object) ['month_cycle' => '03-2026', 'unit_id' => 'U-1', 'category' => 'res', 'usage_units' => 10, 'rooms_count' => 1, 'unit_free_units' => 1, 'net_units' => 9, 'elec_rate' => 55, 'unit_amount' => 495, 'total_attendance' => 30]],
            [(object) ['month_cycle' => '03-2026', 'unit_id' => 'U-1', 'employee_id' => 'E-1', 'employee_name' => 'John', 'attendance' => 30, 'share_units' => 9, 'share_amount' => 495, 'allocation_method' => 'attendance', 'explain_usage_share_units' => 10, 'explain_free_share_units' => 1, 'explain_billable_units' => 9]]
        );

        $res = $this->getJson('/reports/elec-summary?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure([
            'status',
            'month_cycle',
            'unit_rows',
            'share_rows',
            'summary' => ['unit_count', 'share_count'],
        ]);
    }

    public function test_valid_export_request_if_active(): void
    {
        $this->withSession([
            'user_id' => 306,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->times(3)->andReturn((object) ['id' => 21, 'run_status' => 'LOCKED'], (object) ['billed_total' => 500.00], (object) ['recovered_total' => 200.00]);
        DB::shouldReceive('select')->times(3)->andReturn(
            [(object) ['utility_type' => 'ELEC', 'billed_amount' => 500.00]],
            [(object) ['employee_id' => 'E-10', 'billed_amount' => 500.00]],
            [(object) ['employee_id' => 'E-10', 'recovered_amount' => 200.00]]
        );

        $res = $this->get('/export/excel/reconciliation?month_cycle=03-2026');
        $res->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string)$res->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename="reconciliation_03-2026.xlsx"', (string)$res->headers->get('content-disposition'));
        $this->assertStringStartsWith('PK', $res->getContent());
    }

    public function test_monthly_summary_exports_download_headers_and_content_type(): void
    {
        $this->withSession([
            'user_id' => 307,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->times(2)->andReturn((object) ['id' => 44, 'run_status' => 'LOCKED'], (object) ['id' => 44, 'run_status' => 'LOCKED']);
        DB::shouldReceive('select')->times(2)->andReturn(
            [(object) ['utility_type' => 'ELEC', 'total_amount' => 700.00, 'total_qty' => 10.0]],
            [(object) ['utility_type' => 'ELEC', 'total_amount' => 700.00, 'total_qty' => 10.0]]
        );

        $excel = $this->get('/export/excel/monthly-summary?month_cycle=03-2026');
        $excel->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string)$excel->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename="monthly_summary_03-2026.xlsx"', (string)$excel->headers->get('content-disposition'));
        $this->assertStringStartsWith('PK', $excel->getContent());

        $pdf = $this->get('/export/pdf/monthly-summary?month_cycle=03-2026');
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string)$pdf->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename="monthly_summary_03-2026.pdf"', (string)$pdf->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }

    public function test_response_shape_parity_where_proven(): void
    {
        $this->withSession([
            'user_id' => 308,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);

        DB::shouldReceive('selectOne')->once()->andReturn((object) ['id' => 55, 'run_status' => 'APPROVED']);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['billed_total' => 800.00]);
        DB::shouldReceive('select')->once()->andReturn([(object) ['utility_type' => 'WATER', 'billed_amount' => 300.00]]);
        DB::shouldReceive('select')->once()->andReturn([(object) ['employee_id' => 'E-1', 'billed_amount' => 800.00]]);
        DB::shouldReceive('selectOne')->once()->andReturn((object) ['recovered_total' => 100.00]);
        DB::shouldReceive('select')->once()->andReturn([(object) ['employee_id' => 'E-1', 'recovered_amount' => 100.00]]);

        $res = $this->getJson('/reports/reconciliation?month_cycle=03-2026');
        $res->assertOk()->assertJsonStructure([
            'status', 'month_cycle', 'billing_run_id',
            'summary' => ['billed_total', 'recovered_total', 'outstanding_total', 'recovery_ratio'],
            'by_utility', 'by_employee', 'notes'
        ]);
    }
}
