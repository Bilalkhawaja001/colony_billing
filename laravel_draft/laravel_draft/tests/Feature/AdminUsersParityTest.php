<?php

namespace Tests\Feature;

use App\Models\AuthAuditLog;
use App\Models\AuthUser;
use App\Support\FlaskParityAuth;
use Tests\TestCase;

class AdminUsersParityTest extends TestCase
{
    private function seedSuperAdmin(): AuthUser
    {
        return AuthUser::query()->create([
            'username' => 'super',
            'email' => 'super@example.com',
            'password_hash' => FlaskParityAuth::passwordHash('Pass1234!'),
            'role' => 'SUPER_ADMIN',
            'is_active' => 1,
            'force_change_password' => 0,
        ]);
    }

    public function test_admin_users_ui_renders_real_page_for_super_admin(): void
    {
        $admin = $this->seedSuperAdmin();

        $this->withSession([
            'user_id' => $admin->id,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);

        $res = $this->get('/ui/admin/users');
        $res->assertOk();
        $res->assertSee('Admin Users');
        $res->assertSee('super@example.com');
    }

    public function test_admin_users_create_validates_and_inserts_user(): void
    {
        $admin = $this->seedSuperAdmin();
        $this->withSession([
            'user_id' => $admin->id,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);

        $bad = $this->postJson('/api/admin/users/create', [
            'username' => 'u1',
            'email' => 'u1@example.com',
            'role' => 'NOPE',
            'temp_password' => 'short',
        ]);
        $bad->assertStatus(400)->assertJson(['status' => 'error', 'error' => 'invalid input']);

        $ok = $this->postJson('/api/admin/users/create', [
            'username' => 'u1',
            'email' => 'u1@example.com',
            'role' => 'DATA_ENTRY',
            'temp_password' => 'TempPass1!',
            'is_active' => 'true',
        ]);

        $ok->assertOk()->assertJson(['status' => 'ok']);

        $user = AuthUser::query()->where('username', 'u1')->first();
        $this->assertNotNull($user);
        $this->assertSame(1, (int) $user->force_change_password);
        $this->assertTrue(FlaskParityAuth::verifyPassword('TempPass1!', (string) $user->password_hash));
    }

    public function test_admin_users_update_and_reset_password(): void
    {
        $admin = $this->seedSuperAdmin();
        $user = AuthUser::query()->create([
            'username' => 'target',
            'email' => 'target@example.com',
            'password_hash' => FlaskParityAuth::passwordHash('OldPass1!'),
            'role' => 'VIEWER',
            'is_active' => 1,
            'force_change_password' => 0,
        ]);

        $this->withSession([
            'user_id' => $admin->id,
            'role' => 'SUPER_ADMIN',
            'force_change_password' => 0,
        ]);

        $up = $this->postJson('/api/admin/users/update', [
            'id' => $user->id,
            'role' => 'BILLING_ADMIN',
            'is_active' => '0',
        ]);
        $up->assertOk()->assertJson(['status' => 'ok']);

        $user->refresh();
        $this->assertSame('BILLING_ADMIN', $user->role);
        $this->assertSame(0, (int) $user->is_active);

        $badReset = $this->postJson('/api/admin/users/reset-password', [
            'id' => $user->id,
            'temp_password' => 'short',
        ]);
        $badReset->assertStatus(400);

        $okReset = $this->postJson('/api/admin/users/reset-password', [
            'id' => $user->id,
            'temp_password' => 'BrandNew1!',
        ]);
        $okReset->assertOk()->assertJson(['status' => 'ok']);

        $user->refresh();
        $this->assertSame(1, (int) $user->force_change_password);
        $this->assertTrue(FlaskParityAuth::verifyPassword('BrandNew1!', (string) $user->password_hash));
        $this->assertDatabaseHas('auth_audit_log', [
            'event_type' => 'ADMIN_PASSWORD_RESET',
            'user_id' => $user->id,
            'outcome' => 'OK',
        ]);
    }
}
