<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthDraftController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Draft only: DB/auth provider not wired in LIMITED GO batch.
        // Parity target (proven): generic auth failure message, session role/user_id, force_change_password redirect.
        return back()->with('draft_notice', 'Draft-only login handler. No real auth wired yet.');
    }

    public function logout()
    {
        $request = request();
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
        // Draft parity note: response should be generic to avoid account enumeration.
        return back()->with('status', 'If the account exists, reset instructions were sent.');
    }

    public function showResetPassword()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        // Draft only: OTP verify and password update not implemented in this batch.
        return back()->with('draft_notice', 'Draft-only reset handler.');
    }

    public function showProfile()
    {
        return view('profile.index');
    }

    public function changePassword(Request $request)
    {
        // Draft only: hash verify + update + force_change_password clear deferred.
        return response()->json([
            'status' => 'error',
            'error' => 'Draft-only endpoint. Password persistence deferred.',
            'proven_parity_note' => 'Must require old_password verification and clear force_change_password after success',
        ], 501);
    }
}
