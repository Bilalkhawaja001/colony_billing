<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\AllocationEngine;
use Tests\TestCase;

class AllocationEngineHouseRoomStrictnessTest extends TestCase
{
    public function test_house_single_responsible_passes(): void
    {
        $r = AllocationEngine::allocate('U1','HOUSE',100,[],['E1']);
        $this->assertCount(1, $r['allocations']);
    }

    public function test_unknown_residence_type_fails(): void
    {
        $r = AllocationEngine::allocate('U1','MIXED',100,[],[]);
        $this->assertEquals('E_ALLOW_INVALID_TYPE', $r['issues'][0]['code']);
    }
}
