# RELEASE_NOTES v1.0.0-core-go

## Release Scope
Core Laravel migration for `mbs_project` is marked GO.

## Included
- Auth foundation + RBAC + forced password-change enforcement.
- Domain-backed month guard service and middleware integration.
- Billing core endpoints:
  - precheck (real)
  - finalize (real, transaction + replace semantics)
  - lock (real, state-transition guard)
  - approve (parity 410 removed-flow)
- Adjustments/recovery parity behavior (410 where evidence indicates disabled/removed).
- Reconciliation/report surfaces implemented.
- Reconciliation export upgraded to true XLSX output.
- Runtime validation completed and full test suite green.

## Validation Snapshot
- Migrations: successful
- Tests: full suite passed (latest green run)

## Exclusions
- `electric_v1` remains isolated and not merged into core launch scope.

## Operational Notes
- Deploy using DEPLOYMENT_CHECKLIST.md
- Roll back using ROLLBACK_CHECKLIST.md
