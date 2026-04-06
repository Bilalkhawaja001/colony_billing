<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\AllocationEngine;
use Tests\TestCase;

class AllocationEngineTest extends TestCase
{
    public function test_house_requires_single_responsible(): void
    {
        $r = AllocationEngine::allocate('U1','HOUSE',100,[],['E1','E2']);
        $this->assertCount(0, $r['allocations']);
        $this->assertEquals('E_HOUSE_RESP_NOT_SINGLE', $r['issues'][0]['code']);
    }

    public function test_room_zero_attendance_with_consumption_fails(): void
    {
        $r = AllocationEngine::allocate('U1','ROOM',100,[],[]);
        $this->assertEquals('E_UNIT_ZERO_ATT_WITH_CONS', $r['issues'][0]['code']);
    }
}
