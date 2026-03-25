<?php

return [
    // Auth-shell only truth table from proven Flask role paths.
    'path_roles' => [
        '/ui/dashboard' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/reports' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/reconciliation' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'VIEWER'],
        '/ui/profile' => ['SUPER_ADMIN', 'BILLING_ADMIN', 'DATA_ENTRY', 'VIEWER'],
        '/ui/admin/users' => ['SUPER_ADMIN'],
    ],
];
