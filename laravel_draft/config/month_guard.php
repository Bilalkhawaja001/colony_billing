<?php

return [
    // Draft-only policy register from audit exception report.
    'intentional_exceptions' => [
        '/api/billing/finalize',
        '/month/open',
        '/month/transition',
        '/billing/lock',
    ],

    // Shell-level protected write paths (no domain logic here).
    'protected_write_paths' => [
        '/month/open',
        '/month/transition',
        '/billing/lock',
    ],
];
