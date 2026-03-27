<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UiMissingPagesRenderTest extends TestCase
{
    private function actingViewer(): void
    {
        $this->withSession([
            'user_id' => 77,
            'role' => 'VIEWER',
            'force_change_password' => 0,
        ]);
    }

    public static function pageProvider(): array
    {
        return [
            'rates' => ['/ui/rates', 'Rates'],
            'water-meters' => ['/ui/water-meters', 'Water Meters'],
            'van' => ['/ui/van', 'VAN'],
            'employee-master' => ['/ui/employee-master', 'Employee Master'],
            'employees' => ['/ui/employees', 'Employees'],
            'employee-helper' => ['/ui/employee-helper', 'Employee Helper'],
            'unit-master' => ['/ui/unit-master', 'Unit Master'],
            'meter-master' => ['/ui/meter-master', 'Meter Master'],
            'meter-register-ingest' => ['/ui/meter-register-ingest', 'Meter Register Ingest'],
            'rooms' => ['/ui/rooms', 'Rooms'],
            'occupancy' => ['/ui/occupancy', 'Occupancy'],
            'electric-v1-run' => ['/ui/electric-v1-run', 'Electric V1 Run'],
            'electric-v1-outputs' => ['/ui/electric-v1-outputs', 'Electric V1 Outputs'],
            'masters-employees' => ['/ui/masters/employees', 'Masters · Employees'],
            'masters-units' => ['/ui/masters/units', 'Masters · Units'],
            'masters-meters' => ['/ui/masters/meters', 'Masters · Meters'],
            'masters-rates' => ['/ui/masters/rates', 'Masters · Rates'],
            'inputs-mapping' => ['/ui/inputs/mapping', 'Inputs · Mapping'],
            'inputs-hr' => ['/ui/inputs/hr', 'Inputs · HR'],
            'inputs-readings' => ['/ui/inputs/readings', 'Inputs · Readings'],
            'inputs-ro' => ['/ui/inputs/ro', 'Inputs · RO'],
            'finalized-months' => ['/ui/finalized-months', 'Finalized Months'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_missing_ui_pages_render(string $path, string $title): void
    {
        $this->actingViewer();

        $this->get($path)
            ->assertOk()
            ->assertSee($title)
            ->assertSee($path)
            ->assertDontSee('blocked-domain')
            ->assertDontSee('Placeholder');
    }
}
