<?php

return [
    // Protected write endpoints for month-sensitive operations (shell only).
    'protected_write_paths' => [
        '/month/open',
        '/month/transition',
        '/billing/run',
        '/billing/lock',
        '/billing/approve',
        '/rates/upsert',
        '/rates/approve',
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
