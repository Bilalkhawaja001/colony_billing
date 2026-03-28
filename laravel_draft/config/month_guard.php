<?php

return [
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
        '/billing/run',
        '/billing/elec/compute',
        '/billing/water/compute',
        '/imports/meter-register/ingest-preview',
        '/imports/mark-validated',
        '/monthly-rates/initialize',
        '/monthly-rates/config/upsert',
        '/rates/upsert',
        '/rates/approve',
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
