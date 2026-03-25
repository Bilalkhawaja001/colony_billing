<?php

return [
    'roles' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
    'otp_valid_minutes' => 10,
    'otp_max_attempts' => 5,
    'otp_resend_cooldown_sec' => 60,
    'auth_exempt_paths' => ['/login', '/logout', '/forgot-password', '/reset-password'],
];
