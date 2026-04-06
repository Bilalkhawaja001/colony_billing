<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\AttendanceAllocator;
use Tests\TestCase;

class AttendanceAllocatorSplitPrecisionTest extends TestCase
{
    public function test_multi_room_split_precision(): void
    {
        $eligible = ['E1' => 10.0];
        $rows = [
            ['company_id'=>'E1','unit_id'=>'U1','room_id'=>'R1','from_date'=>'2026-03-01','to_date'=>'2026-03-10'],
            ['company_id'=>'E1','unit_id'=>'U1','room_id'=>'R2','from_date'=>'2026-03-11','to_date'=>'2026-03-20'],
        ];
        $out = AttendanceAllocator::allocateRoom($eligible, $rows, []);
        $sum = array_sum(array_map(fn($x)=>(float)$x['attendance_days'], $out['allocations']));
        $this->assertEqualsWithDelta(10.0, $sum, 0.0001);
    }
}
