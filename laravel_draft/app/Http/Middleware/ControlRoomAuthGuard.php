<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ControlRoomAuthGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->hasSession() ? $request->session() : null;

        $sessionKeys = [
            'auth_user_id',
            'billing_user_id',
            'user_id',
            'logged_in_user_id',
            'portal_user_id',
            'auth_user',
            'billing_user',
            'user',
        ];

        $sessionAuthed = false;

        if ($session) {
            foreach ($sessionKeys as $key) {
                if ($session->has($key) && !empty($session->get($key))) {
                    $sessionAuthed = true;
                    break;
                }
            }
        }

        if (auth()->check() || $sessionAuthed) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 'CONTROL_ROOM_AUTH_REQUIRED',
            ], 401);
        }

        return redirect('/login');
    }
}
