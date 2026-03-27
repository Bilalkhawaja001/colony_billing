<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthAuditLog;
use App\Models\AuthUser;
use App\Support\FlaskParityAuth;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    private function parseActive(mixed $value): int
    {
        return in_array(strtolower((string)($value ?? '1')), ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
    }

    private function allowedRoles(): array
    {
        return (array) config('mbs_auth.roles', ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER']);
    }

    public function index()
    {
        $users = AuthUser::query()
            ->select(['id', 'username', 'email', 'role', 'is_active', 'force_change_password', 'created_at'])
            ->orderByDesc('id')
            ->get();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(Request $request)
    {
        $username = trim((string) $request->input('username', ''));
        $email = trim((string) $request->input('email', ''));
        $role = trim((string) $request->input('role', ''));
        $tempPassword = trim((string) $request->input('temp_password', ''));
        $isActive = $this->parseActive($request->input('is_active', '1'));

        if ($username === '' || $email === '' || !in_array($role, $this->allowedRoles(), true) || strlen($tempPassword) < 8) {
            return response()->json(['status' => 'error', 'error' => 'invalid input'], 400);
        }

        try {
            AuthUser::query()->create([
                'username' => $username,
                'email' => $email,
                'password_hash' => FlaskParityAuth::passwordHash($tempPassword),
                'role' => $role,
                'is_active' => $isActive,
                'force_change_password' => 1,
            ]);
        } catch (QueryException) {
            return response()->json(['status' => 'error', 'error' => 'invalid input'], 400);
        }

        return response()->json(['status' => 'ok']);
    }

    public function update(Request $request)
    {
        $uid = (int) $request->input('id', 0);
        $role = trim((string) $request->input('role', ''));
        $isActive = $this->parseActive($request->input('is_active', '1'));

        if ($uid <= 0 || !in_array($role, $this->allowedRoles(), true)) {
            return response()->json(['status' => 'error', 'error' => 'invalid input'], 400);
        }

        AuthUser::query()->where('id', $uid)->update([
            'role' => $role,
            'is_active' => $isActive,
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function resetPassword(Request $request)
    {
        $uid = (int) $request->input('id', 0);
        $tempPassword = trim((string) $request->input('temp_password', ''));

        if ($uid <= 0 || strlen($tempPassword) < 8) {
            return response()->json(['status' => 'error', 'error' => 'invalid input'], 400);
        }

        AuthUser::query()->where('id', $uid)->update([
            'password_hash' => FlaskParityAuth::passwordHash($tempPassword),
            'force_change_password' => 1,
            'updated_at' => now(),
        ]);

        AuthAuditLog::query()->create([
            'event_type' => 'ADMIN_PASSWORD_RESET',
            'username_hint' => '***',
            'user_id' => $uid,
            'outcome' => 'OK',
            'details_json' => '{}',
        ]);

        return response()->json(['status' => 'ok']);
    }
}
