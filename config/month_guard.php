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
        '/api/electric-v1/run',
        '/api/electric-v1/input/allowance/upsert',
        '/api/electric-v1/input/readings/upsert',
        '/api/electric-v1/input/attendance/upsert',
        '/api/electric-v1/input/occupancy/upsert',
        '/api/electric-v1/input/adjustments/upsert',
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
