<?php

namespace App\Services\Sso;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PortalSsoClient
{
    /**
     * @return array{portal_user_id:int,module_key:string,email?:string,name?:string}
     */
    public function verifyToken(string $token, string $ipAddress, ?string $userAgent): array
    {
        $url = (string) config('billing_sso.portal_verify_url', '');
        $secret = (string) config('billing_sso.service_secret', '');
        $timeout = max(1, (int) config('billing_sso.consume_timeout_seconds', 5));

        if ($url === '' || $secret === '') {
            throw new RuntimeException('Billing SSO is not configured.');
        }

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->withToken($secret)
            ->withHeaders([
                'X-Sso-Secret' => $secret,
                'X-Billing-Sso-Secret' => $secret,
            ])
            ->post($url, [
                'token' => $token,
                'module_key' => (string) config('billing_sso.module_key', 'BILLING'),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

        if (! $response->ok()) {
            throw new RuntimeException('Portal SSO verification failed.');
        }

        $payload = $response->json();
        if (! is_array($payload) || ($payload['valid'] ?? false) !== true) {
            throw new RuntimeException('Portal SSO token rejected.');
        }

        $portalUserId = (int) ($payload['portal_user_id'] ?? 0);
        $moduleKey = strtoupper((string) ($payload['module_key'] ?? ''));

        if ($portalUserId <= 0 || $moduleKey !== strtoupper((string) config('billing_sso.module_key', 'BILLING'))) {
            throw new RuntimeException('Portal SSO payload is invalid.');
        }

        return [
            'portal_user_id' => $portalUserId,
            'module_key' => $moduleKey,
            'email' => isset($payload['email']) ? (string) $payload['email'] : null,
            'name' => isset($payload['name']) ? (string) $payload['name'] : null,
        ];
    }
}
