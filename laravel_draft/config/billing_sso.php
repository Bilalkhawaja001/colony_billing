<?php

return [
    'portal_verify_url' => env('BILLING_SSO_PORTAL_VERIFY_URL'),
    'service_secret' => env('BILLING_SSO_SERVICE_SECRET'),
    'consume_timeout_seconds' => (int) env('BILLING_SSO_TIMEOUT_SECONDS', 5),
    'module_key' => env('BILLING_SSO_MODULE_KEY', 'BILLING'),
];
