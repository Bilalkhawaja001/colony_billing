<?php

namespace Tests\Feature;

use Tests\TestCase;

class P1ExecutionOrderTest extends TestCase
{
    private function auth(): void
    {
        $this->withSession([
            'user_id' => 8101,
            'actor_user_id' => 8101,
            'role' => 'BILLING_ADMIN',
            'force_change_password' => 0,
        ]);
    }

    private function assertAcceptableStatus(int $status): void
    {
        $this->assertContains($status, [200, 400, 409, 422]);
    }

    // T-006
    public function test_t006_unit_master_real_page_and_api_reachable(): void
    {
        $this->auth();
        $this->get('/ui/unit-master')->assertOk()->assertSee('Unit Master Workspace')->assertDontSee('Parity draft page is active');
        $r = $this->postJson('/units/upsert', ['unit_id' => 'U-P1-1', 'unit_name' => 'Unit P1']);
        $this->assertAcceptableStatus($r->status());
    }

    // T-007
    public function test_t007_rooms_real_page_and_api_reachable(): void
    {
        $this->auth();
        $this->get('/ui/rooms')->assertOk()->assertSee('Rooms Workspace')->assertDontSee('Parity draft page is active');
        $r = $this->postJson('/rooms/upsert', ['unit_id' => 'U-P1-1', 'room_code' => 'R1', 'room_label' => 'Room 1']);
        $this->assertAcceptableStatus($r->status());
    }

    // T-008
    public function test_t008_occupancy_real_page_and_api_reachable(): void
    {
        $this->auth();
        $this->get('/ui/occupancy')->assertOk()->assertSee('Occupancy Workspace')->assertDontSee('Parity draft page is active');
        $r = $this->postJson('/occupancy/upsert', ['month_cycle' => '05-2026', 'unit_id' => 'U-P1-1', 'employee_id' => 'E-P1-1', 'persons' => 1]);
        $this->assertAcceptableStatus($r->status());
    }

    // T-009
    public function test_t009_meter_master_real_page_and_api_reachable(): void
    {
        $this->auth();
        $this->get('/ui/meter-master')->assertOk()->assertSee('Meter Workspace')->assertDontSee('Parity draft page is active');
        $r1 = $this->postJson('/meter-unit/upsert', ['unit_id' => 'U-P1-1', 'meter_id' => 'M-P1-1']);
        $r2 = $this->postJson('/meter-reading/upsert', ['month_cycle' => '05-2026', 'unit_id' => 'U-P1-1', 'meter_id' => 'M-P1-1', 'usage' => 12, 'amount' => 100]);
        $this->assertAcceptableStatus($r1->status());
        $this->assertAcceptableStatus($r2->status());
    }

    // T-005
    public function test_t005_employee_master_helper_real_pages_and_api_reachable(): void
    {
        $this->auth();
        $this->get('/ui/employee-master')->assertOk()->assertSee('Employee Master Workspace')->assertDontSee('Parity draft page is active');
        $this->get('/ui/employee-helper')->assertOk()->assertSee('Employee Helper Workspace')->assertDontSee('Parity draft page is active');
        $r = $this->postJson('/employees/upsert', ['company_id' => 'E-P1-1', 'employee_name' => 'Emp P1']);
        $this->assertAcceptableStatus($r->status());
    }

    // T-010
    public function test_t010_inputs_pages_real_not_shell(): void
    {
        $this->auth();
        $this->get('/ui/inputs/mapping')->assertOk()->assertSee('Inputs Mapping Workspace')->assertDontSee('Parity draft page is active');
        $this->get('/ui/inputs/hr')->assertOk()->assertSee('Inputs HR Workspace')->assertDontSee('Parity draft page is active');
        $this->get('/ui/inputs/readings')->assertOk()->assertSee('Inputs Readings Workspace')->assertDontSee('Parity draft page is active');
        $this->get('/ui/inputs/ro')->assertOk()->assertSee('Inputs RO Workspace')->assertDontSee('Parity draft page is active');
    }
}
