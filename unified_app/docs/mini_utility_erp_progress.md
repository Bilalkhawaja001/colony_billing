# Mini Utility-ERP Progress

## Completed
- Phase-1: Domain scaffolding + admin rates guardrails + month history UI
- Phase-2: Audit log + deterministic fingerprint endpoint + billing/rates audit hooks
- Phase-3: Locked-month immutability guards + adjustment journal (create/approve/list) + maker-checker on billing approve

## New Endpoints
- `GET /billing/fingerprint?month_cycle=...`
- `POST /billing/adjustments/create`
- `POST /billing/adjustments/approve`
- `GET /billing/adjustments/list?month_cycle=...&employee_id=...`

## Governance Rules Active
- admin-only month open/transition and rates mutation
- rates/expenses/billing-run blocked for LOCKED month
- maker-checker: billing run creator cannot approve same run
- maker-checker: adjustment creator cannot approve same adjustment

## Pending (Phase-4 hardening)
- domain unit tests for deterministic hash reproducibility
- API contract tests for adjustment lifecycle
- final deployment runbook + rollback checklist

## Completed now (UI/UX completion push)
- Billing page upgraded to a single control-center workflow (run/approve/lock/fingerprint)
- Post-lock adjustment UX added (create + approve + live list table)
- API response panel normalized across actions for operator visibility
- Sidebar navigation parity fixed (`Monthly Rates Config` now follows app nav design)
- Reusable status chips and stacked card layout added in global UI theme
