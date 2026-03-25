<?php

return [
    // Shell lock state source. Non-domain placeholder until real month service is wired.
    'state' => [
        'default_locked' => true,
        'session_key' => 'month_guard_locked',
        'header_override' => 'X-Month-Locked', // optional test/dev override: 1|0
    ],

    // Protected write endpoints for month-sensitive operations (shell only).
    'protected_write_paths' => [
        '/month/open',
        '/month/transition',
        '/billing/lock',
        '/billing/approve',
        '/billing/adjustments/create',
        '/billing/adjustments/approve',
        '/recovery/payment',
        '/api/billing/precheck',
        '/api/billing/finalize',
    ],

    // Intentional exception routes that bypass lock block in shell mode.
    'intentional_exceptions' => [
        '/month/transition',
        '/api/billing/finalize',
        '/billing/lock',
        '/billing/approve',
        '/billing/adjustments/create',
        '/billing/adjustments/approve',
        '/recovery/payment',
    ],
];
