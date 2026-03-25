<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthAuditLog;
use App\Models\AuthPasswordResetOtp;
use App\Models\AuthUser;
use App\Support\FlaskParityAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthDraftController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $username = trim((string) $request->input('username', ''));
        $password = trim((string) $request->input('password', ''));

        $user = AuthUser::query()->where('username', $username)->first();

        if (!$user || (int)$user->is_active !== 1 || !FlaskParityAuth::verifyPassword($password, (string)$user->password_hash)) {
            return back()->withErrors(['auth' => 'Invalid credentials or request']);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        session([
            'user_id' => $user->id,
            'role' => $user->role,
            'admin_user_id' => (string) $user->id,
            'actor_user_id' => (string) $user->id,
            'force_change_password' => (int) $user->force_change_password,
        ]);

        return redirect(((int)$user->force_change_password === 1) ? '/ui/profile' : '/ui/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $identity = trim((string)$request->input('identity', ''));
        $user = AuthUser::query()->where('username', $identity)->orWhere('email', $identity)->first();

        if (!$user) {
            return back()->with('status', 'If the account exists, reset instructions were sent.');
        }

        $latest = AuthPasswordResetOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        $cooldown = (int) config('mbs_auth.otp_resend_cooldown_sec', 60);
        if ($latest && $latest->last_sent_at) {
            $last = Carbon::parse($latest->last_sent_at);
            if (now()->diffInSeconds($last) < $cooldown) {
                return back()->with('status', 'If the account exists, reset instructions were sent.');
            }
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = (string) config('app.key', 'base64:dev-key'); // unproven key strategy marker

        AuthPasswordResetOtp::query()->create([
            'user_id' => $user->id,
            'otp_hash' => FlaskParityAuth::otpHash((int)$user->id, $otp, $key),
            'expires_at' => now()->addMinutes((int) config('mbs_auth.otp_valid_minutes', 10)),
            'attempts' => 0,
            'used_at' => null,
            'last_sent_at' => now(),
        ]);

        AuthAuditLog::query()->create([
            'event_type' => 'FORGOT_PASSWORD_OTP_SENT',
            'username_hint' => substr($identity, 0, 3).'***',
            'user_id' => $user->id,
            'outcome' => 'OK',
            'details_json' => json_encode(['delivery' => 'not_wired_draft']),
        ]);

        // NOTE: Delivery channel (email/sms) intentionally not implemented in this batch.
        return back()->with('status', 'If the account exists, reset instructions were sent.');
    }

    public function showResetPassword()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $identity = trim((string)$request->input('identity', ''));
        $otp = trim((string)$request->input('otp', ''));
        $newPassword = trim((string)$request->input('new_password', ''));

        if (strlen($newPassword) < 8 || !preg_match('/^\d{6}$/', $otp)) {
            return back()->withErrors(['auth' => 'Invalid credentials or request']);
        }

        $user = AuthUser::query()->where('username', $identity)->orWhere('email', $identity)->first();
        if (!$user) {
            return back()->withErrors(['auth' => 'Invalid credentials or request']);
        }

        $rec = AuthPasswordResetOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        if (!$rec || (int)$rec->attempts >= (int) config('mbs_auth.otp_max_attempts', 5) || now()->greaterThan(Carbon::parse($rec->expires_at))) {
            return back()->withErrors(['auth' => 'Invalid credentials or request']);
        }

        $key = (string) config('app.key', 'base64:dev-key');
        if (!hash_equals((string)$rec->otp_hash, FlaskParityAuth::otpHash((int)$user->id, $otp, $key))) {
            $rec->attempts = (int)$rec->attempts + 1;
            $rec->save();
            return back()->withErrors(['auth' => 'Invalid credentials or request']);
        }

        $rec->used_at = now();
        $rec->save();

        $user->password_hash = FlaskParityAuth::passwordHash($newPassword);
        $user->force_change_password = 0;
        $user->save();

        AuthAuditLog::query()->create([
            'event_type' => 'PASSWORD_RESET_COMPLETED',
            'username_hint' => substr($identity, 0, 3).'***',
            'user_id' => $user->id,
            'outcome' => 'OK',
            'details_json' => '{}',
        ]);

        return redirect('/login')->with('status', 'Password reset successful');
    }

    public function showProfile()
    {
        return view('profile.index');
    }

    public function changePassword(Request $request)
    {
        $uid = (int) session('user_id');
        $user = AuthUser::query()->find($uid);

        if (!$user) {
            return response()->json(['status' => 'error', 'error' => 'authentication required'], 401);
        }

        $old = trim((string)$request->input('old_password', ''));
        $new = trim((string)$request->input('new_password', ''));

        if (!FlaskParityAuth::verifyPassword($old, (string)$user->password_hash)) {
            return response()->json(['status' => 'error', 'error' => 'Invalid credentials or request'], 400);
        }

        if (strlen($new) < 8) {
            return response()->json(['status' => 'error', 'error' => 'new_password too short'], 400);
        }

        $user->password_hash = FlaskParityAuth::passwordHash($new);
        $user->force_change_password = 0;
        $user->save();

        session(['force_change_password' => 0]);

        return response()->json(['status' => 'ok']);
    }
}
