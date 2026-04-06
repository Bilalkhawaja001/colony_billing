# FLASK_BILLING_AUTHORITATIVE_CONTRACT

## Purpose
Freeze authoritative billing contract for migration work.

## Decision (frozen)
For Flask `unified_app/api/app.py`, **authoritative runtime billing contract is util-prefixed tables** used by active routes:
- `util_billing_run`
- `util_billing_line`
- `util_month_cycle`
- `util_rate_monthly`
- `util_monthly_rates_config`
- `util_recovery_payment`
- `util_formula_result`
- `util_drinking_formula_result`
- `util_school_van_monthly_charge`
- `util_elec_employee_share_monthly`
- `util_water_employee_share_monthly`

## Why
Active billing routes and reports read/write util-prefixed tables directly.

Evidence:
- Billing run path uses util tables: `unified_app/api/app.py:3485-3577`
- Lock/report/recovery paths use util tables: `app.py:3599+`, `3705+`, `3760+`
- Month/rates paths use util month/rate tables: `app.py:1685+`, `1715+`, `1787+`

## Non-authoritative legacy contract
The following DDL section exists but is **not authoritative for current billing runtime**:
- `billing_run`
- `billing_rows`

Evidence: DDL block at `unified_app/api/app.py:4337+`.

## Enforcement rule for Laravel remediation
- Any Laravel billing service query touching month/rate/run/line must target the authoritative util contract.
- No new migration/service should introduce `billing_run` aliasing for core billing flow.

## Change control
If contract is changed later, update this file + parity matrix + migration ownership checker in same PR.