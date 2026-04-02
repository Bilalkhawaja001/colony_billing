<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransportModuleTest extends TestCase
{
    private function authAsDataEntry(): void
    {
        $this->withSession([
            'user_id' => 7,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);
    }

    private function seedMonth(string $monthCycle, string $state = 'OPEN'): void
    {
        DB::table('util_month_cycle')->updateOrInsert(
            ['month_cycle' => $monthCycle],
            [
                'state' => $state,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function createVehicle(string $code = 'VAN-01', string $name = 'School Van'): int
    {
        return DB::table('transport_vehicles')->insertGetId([
            'vehicle_code' => $code,
            'vehicle_name' => $name,
            'is_active' => 1,
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_summary_calculation_applies_transport_formula_correctly(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('04-2026', 'OPEN');
        $vehicleId = $this->createVehicle();

        DB::table('transport_rent_entries')->insert([
            'month_cycle' => '04-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transport_fuel_entries')->insert([
            'month_cycle' => '04-2026',
            'entry_date' => '2026-04-02',
            'vehicle_id' => $vehicleId,
            'fuel_liters' => 10,
            'fuel_price' => 50,
            'fuel_cost' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transport_adjustments')->insert([
            ['month_cycle' => '04-2026', 'vehicle_id' => $vehicleId, 'direction' => 'plus', 'amount' => 100, 'reason' => 'Extra route', 'created_at' => now(), 'updated_at' => now()],
            ['month_cycle' => '04-2026', 'vehicle_id' => $vehicleId, 'direction' => 'minus', 'amount' => 40, 'reason' => 'Correction', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->getJson('/api/transport/summary?month_cycle=04-2026');
        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('rows.0.total_cost', 1500)
            ->assertJsonPath('rows.0.company_share', 750)
            ->assertJsonPath('rows.0.father_share', 750)
            ->assertJsonPath('rows.0.net_father_bill', 810)
            ->assertJsonPath('totals.total_cost', 1500)
            ->assertJsonPath('totals.net_father_bill', 810);
    }

    public function test_rent_upsert_updates_existing_row_without_duplicate(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('04-2026', 'OPEN');
        $vehicleId = $this->createVehicle();

        $this->postJson('/api/transport/rent-entries/upsert', [
            'month_cycle' => '04-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 1200,
        ])->assertOk()->assertJsonPath('message', 'Rent entry created successfully.');

        $this->postJson('/api/transport/rent-entries/upsert', [
            'month_cycle' => '04-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 1800,
        ])->assertOk()->assertJsonPath('message', 'Rent entry updated successfully.');

        $this->assertSame(1, DB::table('transport_rent_entries')->where('month_cycle', '04-2026')->where('vehicle_id', $vehicleId)->count());
        $this->assertSame('1800', (string) DB::table('transport_rent_entries')->where('month_cycle', '04-2026')->where('vehicle_id', $vehicleId)->value('rent_amount'));
    }

    public function test_fuel_upsert_auto_calculates_cost_and_edit_by_id_updates_same_row(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('04-2026', 'OPEN');
        $vehicleId = $this->createVehicle();

        $create = $this->postJson('/api/transport/fuel-entries/upsert', [
            'month_cycle' => '04-2026',
            'entry_date' => '2026-04-02',
            'vehicle_id' => $vehicleId,
            'fuel_liters' => 12.5,
            'fuel_price' => 40,
        ]);

        $create->assertOk()
            ->assertJsonPath('message', 'Fuel entry saved successfully.')
            ->assertJsonPath('fuel_cost', 500);

        $fuelId = (int) $create->json('record_id');

        $edit = $this->postJson('/api/transport/fuel-entries/upsert', [
            'id' => $fuelId,
            'month_cycle' => '04-2026',
            'entry_date' => '2026-04-03',
            'vehicle_id' => $vehicleId,
            'fuel_liters' => 20,
            'fuel_price' => 55,
        ]);

        $edit->assertOk()
            ->assertJsonPath('message', 'Fuel entry updated successfully.')
            ->assertJsonPath('fuel_cost', 1100);

        $this->assertSame(1, DB::table('transport_fuel_entries')->count());
        $this->assertSame('1100', (string) DB::table('transport_fuel_entries')->where('id', $fuelId)->value('fuel_cost'));
        $this->assertSame('2026-04-03', DB::table('transport_fuel_entries')->where('id', $fuelId)->value('entry_date'));
    }

    public function test_adjustment_upsert_validation_and_global_adjustment_handling(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('04-2026', 'OPEN');
        $vehicleId = $this->createVehicle();

        $this->postJson('/api/transport/adjustments/upsert', [
            'month_cycle' => '04-2026',
            'vehicle_id' => null,
            'direction' => 'plus',
            'amount' => 250,
            'reason' => 'Global recovery',
        ])->assertOk()->assertJsonPath('message', 'Adjustment saved successfully.');

        $this->assertNull(DB::table('transport_adjustments')->where('reason', 'Global recovery')->value('vehicle_id'));

        $this->postJson('/api/transport/adjustments/upsert', [
            'month_cycle' => '04-2026',
            'vehicle_id' => $vehicleId,
            'direction' => 'bad-direction',
            'amount' => 100,
            'reason' => 'Invalid direction',
        ])->assertStatus(422)->assertJsonValidationErrors(['direction']);
    }

    public function test_locked_month_guard_blocks_month_bound_writes_but_allows_summary_and_vehicle_master(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('05-2026', 'LOCKED');
        $vehicleId = $this->createVehicle();

        $this->getJson('/api/transport/summary?month_cycle=05-2026')
            ->assertOk()
            ->assertJsonPath('month_lock.is_locked', true)
            ->assertJsonPath('month_lock.state', 'LOCKED');

        $this->postJson('/api/transport/rent-entries/upsert', [
            'month_cycle' => '05-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 1000,
        ])->assertStatus(409)
            ->assertJsonPath('action', 'rent_entry_upsert')
            ->assertJsonPath('lock_state', 'LOCKED');

        $this->postJson('/api/transport/fuel-entries/upsert', [
            'month_cycle' => '05-2026',
            'entry_date' => '2026-05-01',
            'vehicle_id' => $vehicleId,
            'fuel_liters' => 5,
            'fuel_price' => 10,
        ])->assertStatus(409)
            ->assertJsonPath('action', 'fuel_entry_upsert')
            ->assertJsonPath('lock_state', 'LOCKED');

        $this->postJson('/api/transport/adjustments/upsert', [
            'month_cycle' => '05-2026',
            'direction' => 'plus',
            'amount' => 100,
            'reason' => 'Blocked',
        ])->assertStatus(409)
            ->assertJsonPath('action', 'adjustment_upsert')
            ->assertJsonPath('lock_state', 'LOCKED');

        $this->postJson('/api/transport/vehicles/upsert', [
            'id' => $vehicleId,
            'vehicle_code' => 'VAN-01',
            'vehicle_name' => 'Locked Month Vehicle Update',
            'is_active' => true,
        ])->assertOk()->assertJsonPath('message', 'Vehicle updated successfully.');
    }

    public function test_zero_open_month_returns_valid_summary_structure_without_crash(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('06-2026', 'OPEN');

        $response = $this->getJson('/api/transport/summary?month_cycle=06-2026');

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('month_cycle', '06-2026')
            ->assertJsonPath('month_lock.state', 'OPEN')
            ->assertJsonPath('month_lock.is_locked', false)
            ->assertJsonStructure([
                'status',
                'month_cycle',
                'month_lock' => ['state', 'is_locked'],
                'formula',
                'vehicles',
                'rows',
                'totals',
                'rent_entries',
                'fuel_entries',
                'adjustments',
            ]);
    }

    public function test_transport_successful_writes_create_audit_entries_only_after_success(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('07-2026', 'OPEN');
        $this->seedMonth('08-2026', 'LOCKED');

        $vehicleCreate = $this->postJson('/api/transport/vehicles/upsert', [
            'vehicle_code' => 'VAN-AUDIT',
            'vehicle_name' => 'Audit Van',
            'is_active' => true,
        ])->assertOk();

        $vehicleId = (int) $vehicleCreate->json('record_id');

        $this->postJson('/api/transport/rent-entries/upsert', [
            'month_cycle' => '07-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 2000,
        ])->assertOk();

        $this->postJson('/api/transport/fuel-entries/upsert', [
            'month_cycle' => '07-2026',
            'entry_date' => '2026-07-02',
            'vehicle_id' => $vehicleId,
            'fuel_liters' => 10,
            'fuel_price' => 60,
        ])->assertOk();

        $this->postJson('/api/transport/adjustments/upsert', [
            'month_cycle' => '07-2026',
            'direction' => 'plus',
            'amount' => 150,
            'reason' => 'Audit adjustment',
        ])->assertOk();

        $this->postJson('/api/transport/adjustments/upsert', [
            'month_cycle' => '07-2026',
            'direction' => 'bad',
            'amount' => 100,
            'reason' => 'Should fail',
        ])->assertStatus(422);

        $this->postJson('/api/transport/rent-entries/upsert', [
            'month_cycle' => '08-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 999,
        ])->assertStatus(409);

        $this->assertSame(4, DB::table('util_audit_log')->where('entity_type', 'transport')->count());

        $vehicleAudit = DB::table('util_audit_log')->where('entity_type', 'transport')->where('action', 'vehicle_upsert')->first();
        $this->assertNotNull($vehicleAudit);
        $this->assertSame((string) $vehicleId, $vehicleAudit->entity_id);
        $this->assertSame('7', $vehicleAudit->actor_user_id);

        $rentAudit = DB::table('util_audit_log')->where('entity_type', 'transport')->where('action', 'rent_entry_upsert')->first();
        $this->assertNotNull($rentAudit);
        $this->assertStringContainsString('"module":"transport"', (string) $rentAudit->after_json);
        $this->assertStringContainsString('"month_cycle":"07-2026"', (string) $rentAudit->after_json);
        $this->assertNull($rentAudit->before_json);

        $fuelAudit = DB::table('util_audit_log')->where('entity_type', 'transport')->where('action', 'fuel_entry_upsert')->first();
        $this->assertNotNull($fuelAudit);
        $this->assertStringContainsString('"record_type":"fuel_entry"', (string) $fuelAudit->after_json);

        $adjustmentAudit = DB::table('util_audit_log')->where('entity_type', 'transport')->where('action', 'adjustment_upsert')->first();
        $this->assertNotNull($adjustmentAudit);
        $this->assertStringContainsString('"record_type":"adjustment"', (string) $adjustmentAudit->after_json);
    }

    public function test_summary_includes_father_bill_preview_shape(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('09-2026', 'OPEN');
        $vehicleId = $this->createVehicle('VAN-FB', 'Father Bill Van');

        DB::table('transport_rent_entries')->insert([
            'month_cycle' => '09-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transport_fuel_entries')->insert([
            'month_cycle' => '09-2026',
            'entry_date' => '2026-09-01',
            'vehicle_id' => $vehicleId,
            'fuel_liters' => 5,
            'fuel_price' => 100,
            'fuel_cost' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/transport/summary?month_cycle=09-2026')
            ->assertOk()
            ->assertJsonPath('father_bill.month_cycle', '09-2026')
            ->assertJsonPath('father_bill.total_rent', 1000)
            ->assertJsonPath('father_bill.total_fuel_cost', 500)
            ->assertJsonPath('father_bill.total_cost', 1500)
            ->assertJsonPath('father_bill.father_share', 750)
            ->assertJsonStructure([
                'father_bill' => [
                    'month_cycle',
                    'total_rent',
                    'total_fuel_cost',
                    'total_cost',
                    'company_share',
                    'father_share',
                    'plus_adjustments',
                    'minus_adjustments',
                    'net_father_bill',
                    'vehicle_rows',
                ],
            ]);
    }

    public function test_csv_export_returns_selected_month_and_key_totals_and_locked_month_is_allowed(): void
    {
        $this->authAsDataEntry();
        $this->seedMonth('10-2026', 'LOCKED');
        $vehicleId = $this->createVehicle('VAN-CSV', 'CSV Van');

        DB::table('transport_rent_entries')->insert([
            'month_cycle' => '10-2026',
            'vehicle_id' => $vehicleId,
            'rent_amount' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('transport_fuel_entries')->insert([
            'month_cycle' => '10-2026',
            'entry_date' => '2026-10-01',
            'vehicle_id' => $vehicleId,
            'fuel_liters' => 10,
            'fuel_price' => 50,
            'fuel_cost' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/api/transport/export/csv?month_cycle=10-2026');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('TRANSPORT MONTHLY FATHER BILL', $content);
        $this->assertStringContainsString('Month Cycle,10-2026', $content);
        $this->assertStringContainsString('total_cost,1500.00', $content);
        $this->assertStringContainsString('father_share,750.00', $content);
        $this->assertStringContainsString('net_father_bill,750.00', $content);
        $this->assertStringContainsString('"CSV Van","VAN-CSV",1000.00,500.00,1500.00,750.00,0.00,0.00,750.00', $content);
    }
}
