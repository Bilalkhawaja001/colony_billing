<?php

namespace Tests\Unit\ElectricV1;

use App\Services\ElectricV1\Domain\ConsumptionEngine;
use Tests\TestCase;

class ConsumptionEngineTest extends TestCase
{
    public function test_reverse_read_is_rejected(): void
    {
        $out = ConsumptionEngine::compute('U1', ['reading_status'=>'NORMAL','previous_reading'=>100,'current_reading'=>90], []);
        $this->assertNull($out['result']);
        $this->assertEquals('E_READ_REVERSE', $out['issues'][0]['code']);
    }

    public function test_faulty_uses_history_average(): void
    {
        $hist = [
            ['reading_status'=>'NORMAL','previous_reading'=>10,'current_reading'=>20,'cycle_start_date'=>'2026-01-01','cycle_end_date'=>'2026-01-31'],
            ['reading_status'=>'NORMAL','previous_reading'=>20,'current_reading'=>35,'cycle_start_date'=>'2026-02-01','cycle_end_date'=>'2026-02-28'],
        ];
        $out = ConsumptionEngine::compute('U1', ['reading_status'=>'FAULTY'], $hist);
        $this->assertNotNull($out['result']);
        $this->assertTrue($out['result']['is_estimated']);
    }
}
