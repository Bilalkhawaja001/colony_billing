# LAUNCH_READINESS_GAP_REPORT

## Launch-Ready (Current LIMITED GO)
- Auth + RBAC + forced-password-change gates
- Billing precheck/finalize/lock core boundaries
- Approve parity (410 removed flow)
- Adjustments create/approve parity (410 removed flow)
- Recovery payment parity (410 disabled flow)
- Reconciliation report (read-only)
- Active report surfaces implemented:
  - /reports/monthly-summary
  - /reports/recovery
  - /reports/employee-bill-summary
  - /reports/van
  - /reports/elec-summary
- Active export surface implemented:
  - /export/excel/reconciliation (LIMITED GO CSV adapter)

## Launch-Blocking
- Full XLSX/PDF binary export parity not yet implemented (csv adapter used for active excel route)
- finalize computation internals still draft approximation (boundary and guards are real)
- month guard still shell-driven (config/session), not full domain month-state service

## Post-Launch Queue
- audit-log parity for lock/finalize and report accesses
- deterministic output fixture parity checks against Flask for monthly/recovery/employee/van/elec reports
- tighten schema/index assumptions on util_* tables for production DB engines

## Separate Module
- electric_v1 remains separate-module scope; not part of launch-critical core in this batch
