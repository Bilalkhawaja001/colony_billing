<?php

namespace Tests\Feature;

use Tests\TestCase;

class ElectricV1UiTest extends TestCase
{
    public function test_ui_pages_render_for_allowed_roles(): void
    {
        $this->withSession(['user_id'=>1,'role'=>'BILLING_ADMIN','force_change_password'=>0]);
        $this->get('/ui/electric-v1-run')->assertOk();

        $this->withSession(['user_id'=>2,'role'=>'VIEWER','force_change_password'=>0]);
        $this->get('/ui/electric-v1-outputs')->assertOk();
    }
}
