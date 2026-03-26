<?php

namespace Tests\Feature\ElectricV1;

use Tests\TestCase;

class ElectricV1UiRoleGuardTest extends TestCase
{
    public function test_viewer_cannot_access_run_page_but_can_access_outputs_page(): void
    {
        $this->withSession(['user_id'=>2,'role'=>'VIEWER','force_change_password'=>0]);
        $this->get('/ui/electric-v1-run')->assertStatus(403);
        $this->get('/ui/electric-v1-outputs')->assertStatus(200);
    }
}
