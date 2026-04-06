<?php

return [
    // Auth-shell only truth table from proven Flask role paths.
    'path_roles' => [
        '/ui/dashboard' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/reports' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/reconciliation' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'VIEWER'],
        '/ui/elec-summary' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'VIEWER'],
        '/ui/profile' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/month-control' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/monthly-setup' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/imports' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/family-details' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY'],
        '/ui/results/employee-wise' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/results/unit-wise' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/logs' => ['SUPER_ADMIN'],
        '/ui/admin/users' => ['SUPER_ADMIN'],
    ],
];
