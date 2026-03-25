# MONTH_LOCK_COVERAGE_REPORT

## Scoped Write-Route Guard Coverage
- Direct guard coverage exists on routes calling month guard helpers.
- Some routes intentionally bypass direct month-lock checks due to control semantics.

## Exception Register (Scoped)
1. `POST /api/billing/finalize`
   - Intentional bypass: finalize rerun semantics with idempotent replacement.
2. `POST /month/open`
   - Intentional bypass: month state control endpoint.
3. `POST /month/transition`
   - Intentional bypass: month state control endpoint.
4. `POST /billing/lock`
   - Indirect guard via state preconditions (month/run-state checks).

## Status
- Exception register completeness (scoped): **Closed**
- Governance sign-off for intentional exceptions: **Partial**
