<?php

namespace Tests\Feature\ElectricV1;

use Tests\TestCase;

class ElectricV1InputUpsertConflictTest extends TestCase
{
    public function test_invalid_reading_status_rejected(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'DATA_ENTRY','force_change_password'=>0]);
        $this->postJson('/api/electric-v1/input/readings/upsert', ['rows'=>[[
            'cycle_start_date'=>'2026-03-01','cycle_end_date'=>'2026-03-31','unit_id'=>'U1','previous_reading'=>1,'current_reading'=>2,'reading_status'=>'BAD'
        ]]])->assertStatus(422);
    }

    public function test_negative_attendance_rejected(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'DATA_ENTRY','force_change_password'=>0]);
        $this->postJson('/api/electric-v1/input/attendance/upsert', ['rows'=>[[
            'cycle_start_date'=>'2026-03-01','cycle_end_date'=>'2026-03-31','company_id'=>'E1','attendance_days'=>-1
        ]]])->assertStatus(422);
    }
}
