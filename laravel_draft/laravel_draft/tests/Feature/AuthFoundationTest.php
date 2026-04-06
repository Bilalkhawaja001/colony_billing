<?php

namespace Tests\Feature;

use App\Models\AuthPasswordResetOtp;
use App\Models\AuthUser;
use App\Support\FlaskParityAuth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthFoundationTest extends TestCase
{
    protected function seedUser(array $overrides = []): AuthUser
    {
        return AuthUser::query()->create(array_merge([
            'username' => 'tester',
            'email' => 'tester@example.com',
            'password_hash' => FlaskParityAuth::passwordHash('Pass1234!'),
            'role' => 'DATA_ENTRY',
            'is_active' => 1,
            'force_change_password' => 0,
        ], $overrides));
    }

    public function test_login_success_redirects_dashboard(): void
    {
        $this->seedUser();

        $res = $this->post('/login', ['username' => 'tester', 'password' => 'Pass1234!']);
        $res->assertRedirect('/ui/dashboard');
    }

    public function test_login_fail_uses_generic_message(): void
    {
        $this->seedUser();

        $res = $this->from('/login')->post('/login', ['username' => 'tester', 'password' => 'bad']);
        $res->assertRedirect('/login');
        $res->assertSessionHasErrors('auth');
    }

    public function test_forced_password_change_redirects_profile(): void
    {
        $this->seedUser(['force_change_password' => 1]);

        $this->post('/login', ['username' => 'tester', 'password' => 'Pass1234!']);
        $res = $this->get('/ui/dashboard');
        $res->assertRedirect('/ui/profile');
    }

    public function test_protected_route_blocks_unauthenticated(): void
    {
        $res = $this->get('/ui/dashboard');
        $res->assertRedirect('/login');
    }

    public function test_role_denied_for_admin_users_route(): void
    {
        $this->withSession([
            'user_id' => 1,
            'role' => 'DATA_ENTRY',
            'force_change_password' => 0,
        ]);

        $res = $this->get('/ui/admin/users');
        $res->assertStatus(403);
    }

    public function test_reset_flow_happy_path(): void
    {
        $user = $this->seedUser();
        $key = (string) config('app.key', 'base64:dev-key');
        $otp = '123456';

        AuthPasswordResetOtp::query()->create([
            'user_id' => $user->id,
            'otp_hash' => FlaskParityAuth::otpHash($user->id, $otp, $key),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'used_at' => null,
            'last_sent_at' => now(),
        ]);

        $res = $this->post('/reset-password', [
            'identity' => 'tester',
            'otp' => $otp,
            'new_password' => 'NewPass123!'
        ]);

        $res->assertRedirect('/login');
        $user->refresh();
        $this->assertTrue(FlaskParityAuth::verifyPassword('NewPass123!', $user->password_hash));
    }
}
