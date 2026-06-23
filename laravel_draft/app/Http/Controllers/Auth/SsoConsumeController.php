<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthAuditLog;
use App\Models\BillingPortalUserLink;
use App\Services\Sso\PortalSsoClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SsoConsumeController extends Controller
{
    public function __invoke(Request $request, PortalSsoClient $client): RedirectResponse
    {
        $token = trim((string) $request->query('token', ''));
        if ($token === '') {
            return redirect('/login')->withErrors(['auth' => 'Missing SSO token.']);
        }

        try {
            $payload = $client->verifyToken($token, $request->ip() ?? '', $request->userAgent());

            $link = BillingPortalUserLink::query()
                ->with('billingUser')
                ->where('portal_user_id', (int) $payload['portal_user_id'])
                ->where('module_key', strtoupper((string) $payload['module_key']))
                ->where('is_active', true)
                ->first();

            $billingUser = $link?->billingUser;
            if (! $link || ! $billingUser || (int) $billingUser->is_active !== 1) {
                AuthAuditLog::query()->create([
                    'event_type' => 'SSO_LOGIN_FAILED',
                    'username_hint' => isset($payload['email']) ? substr((string) $payload['email'], 0, 3).'***' : null,
                    'user_id' => null,
                    'outcome' => 'FAIL',
                    'details_json' => json_encode([
                        'reason' => 'missing_or_inactive_billing_mapping',
                        'portal_user_id' => (int) $payload['portal_user_id'],
                        'module_key' => (string) $payload['module_key'],
                    ]),
                ]);

                return redirect('/login')->withErrors(['auth' => 'Billing access is not mapped for this portal account.']);
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put([
                'user_id' => (int) $billingUser->id,
                'role' => (string) ($link->billing_role ?: $billingUser->role),
                'portal_user_id' => (int) $payload['portal_user_id'],
                'admin_user_id' => (string) $billingUser->id,
                'actor_user_id' => (string) $billingUser->id,
                'force_change_password' => (int) $billingUser->force_change_password,
                'sso_module_key' => (string) $payload['module_key'],
            ]);
            $request->session()->regenerate();

            AuthAuditLog::query()->create([
                'event_type' => 'SSO_LOGIN_SUCCESS',
                'username_hint' => substr((string) $billingUser->username, 0, 3).'***',
                'user_id' => (int) $billingUser->id,
                'outcome' => 'OK',
                'details_json' => json_encode([
                    'portal_user_id' => (int) $payload['portal_user_id'],
                    'module_key' => (string) $payload['module_key'],
                    'role' => (string) ($link->billing_role ?: $billingUser->role),
                ]),
            ]);

            return redirect(((int) $billingUser->force_change_password === 1) ? '/ui/profile' : '/dashboard');
        } catch (\Throwable $e) {
            Log::warning('Billing SSO consume failed', ['error' => $e->getMessage()]);

            return redirect('/login')->withErrors(['auth' => 'SSO login failed. Use emergency billing login if authorized.']);
        }
    }
}
