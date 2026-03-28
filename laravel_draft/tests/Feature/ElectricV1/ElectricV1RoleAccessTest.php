<?php

namespace Tests\Feature\ElectricV1;

use Tests\TestCase;

class ElectricV1RoleAccessTest extends TestCase
{
    public function test_viewer_cannot_post_write_endpoints(): void
    {
        $this->withSession(['user_id'=>11,'role'=>'VIEWER','force_change_password'=>0]);
        $this->postJson('/api/electric-v1/run', ['cycle_start'=>'2026-03-01','cycle_end'=>'2026-03-31','flat_rate'=>2])->assertStatus(403);
        $this->postJson('/api/electric-v1/input/allowance/upsert', ['rows'=>[]])->assertStatus(403);
    }

    public function test_viewer_can_read_endpoints(): void
    {
        $this->withSession(['user_id'=>12,'role'=>'VIEWER','force_change_password'=>0]);
        $this->getJson('/api/electric-v1/outputs?cycle_start=2026-03-01&cycle_end=2026-03-31')->assertStatus(200);
        $this->getJson('/api/electric-v1/exceptions?cycle_start=2026-03-01&cycle_end=2026-03-31')->assertStatus(200);
        $this->getJson('/api/electric-v1/runs?cycle_start=2026-03-01&cycle_end=2026-03-31')->assertStatus(200);
    }
}
