# DB_CONTRACT_AND_SCHEMA_AUDIT

## 1) Schema inventory summary

### Flask
- EV1 schema via SQL migration: `unified_app/api/migrations/20260322_create_electric_v1_tables.sql`
- Additional runtime DDL in `unified_app/api/app.py` (multiple `CREATE TABLE IF NOT EXISTS`).

### Laravel
- Migrations include auth, EV1 core, unit/occupancy/employees, family, meter, water-zone, refs.
- Billing service expects additional util billing tables (`util_billing_run`, `util_billing_line`, `util_month_cycle`, `util_rate_monthly`, etc.) not explicitly created in current migration set.

## 2) Critical contract mismatches

| Issue | Severity | Evidence | Impact |
|---|---|---|---|
| Flask mixed table contracts (`billing_run` vs `util_billing_run`) | Critical | `app.py:4337+` and `app.py:3485+` | Operational ambiguity and migration errors likely. |
| Laravel service-table dependency without owning migrations | Critical | `DraftBillingFlowService.php` SQL refs; absence in `database/migrations/*` | New environment can fail at runtime despite code/tests in seeded DBs. |
| Month/rate table dependency via SQL not guaranteed by migrations | High | `ImportsMonthlySetupService` uses `util_month_cycle`; service SQL across billing/reporting | Workflow may break on fresh DB where tables not pre-provisioned. |

## 3) EV1 contract status
- EV1 tables are present in both apps and structurally mirrored enough for parity tests.
- Evidence:
  - Flask SQL: `electric_v1_*` tables in `20260322_create_electric_v1_tables.sql`
  - Laravel migration: `2026_03_26_170001_create_electric_v1_core_tables.php`

## 4) Uniqueness/nullability risk notes
- EV1 duplicate key protections are explicitly tested and validated in both stacks (route/service logic + tests).
- Non-EV1 util billing tables rely on implicit existing schema and SQL assumptions; uniqueness/constraints are not fully documented by migrations in Laravel.

## 5) Verdict
**Schema parity is partial. EV1 contract is relatively strong; full billing contract is fragile due migration ownership gaps and naming drift.**