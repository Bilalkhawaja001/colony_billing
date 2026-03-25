<?php

use App\Models\AuthUser;
use App\Support\FlaskParityAuth;
use Illuminate\Support\Facades\Artisan;

Artisan::command('mbs:about', function () {
    $this->comment('MBS Laravel Draft (auth-only LIMITED GO)');
});

Artisan::command('mbs:auth:hash {password}', function (string $password) {
    $this->line(FlaskParityAuth::passwordHash($password));
})->purpose('Generate Flask-compatible pbkdf2_sha256 hash for auth_users.password_hash');

Artisan::command('mbs:auth:user-create {username} {email} {password} {role=DATA_ENTRY} {--force-change=1} {--inactive=0}',
    function (string $username, string $email, string $password, string $role) {
        $roles = config('mbs_auth.roles', []);
        if (!in_array($role, $roles, true)) {
            $this->error('Invalid role. Allowed: '.implode(', ', $roles));
            return;
        }

        $user = AuthUser::query()->updateOrCreate(
            ['username' => $username],
            [
                'email' => $email,
                'password_hash' => FlaskParityAuth::passwordHash($password),
                'role' => $role,
                'is_active' => ((int)$this->option('inactive') === 1) ? 0 : 1,
                'force_change_password' => ((int)$this->option('force-change') === 1) ? 1 : 0,
            ]
        );

        $this->info('User ready: #'.$user->id.' '.$user->username.' role='.$user->role);
        $this->line('Session keys on login: user_id, role, admin_user_id, actor_user_id, force_change_password');
    }
)->purpose('Create/update auth test user for local verification');
