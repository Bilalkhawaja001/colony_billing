# DEPENDENCY_CHAIN_MAP

## High-level chain
Month State (T-002) -> Rates (T-004) -> Imports (T-003) -> Billing Workspace (T-001) -> Reporting/Reconciliation depth (T-016)

## Detailed dependencies
- T-001 Billing workspace depends on: T-002, T-003, T-004
- T-005 Employee modules depend on: T-003 (import flows)
- T-006 Unit master has no hard dependency
- T-007 Rooms depends on: T-006
- T-008 Occupancy depends on: T-006, T-007
- T-009 Meter module depends on: T-006
- T-010 Inputs pages depend on: T-003, T-009
- T-011 Water module depends on: T-008
- T-012 Van module depends on: T-004
- T-013 Elec summary depends on: T-001
- T-014 Family/results/logs depth has no hard dependency
- T-015 Finalized months depends on: T-002
- T-016 Dashboard/reports/reconciliation depth depends on: T-001, T-011, T-012

## Blocker nodes
1. T-002 failure blocks state-aware UX across billing/rates/imports.
2. T-004 failure blocks accurate billing operator UX.
3. T-003 failure blocks reliable data readiness path before billing execution.
4. T-001 failure blocks core operator parity objective.

## Parallelizable work after P0
- T-006 and T-014 can run parallel.
- T-005 can start once T-003 stable.
- T-011/T-012 can proceed after respective dependencies complete.