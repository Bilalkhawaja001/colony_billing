# BILLING_FINALIZE_PROOF

## Proven Transaction Wrapper
- Source: `unified_app/api/app.py` function `exec_txn`
- Behavior (Proven):
  - opens sqlite connection
  - executes callback
  - `commit()` on success
  - `rollback()` on exception
  - always `close()` in finally

## Finalize Chain (Scoped)
- `POST /api/billing/precheck` -> `api_billing_precheck`
  - read-only precheck path
  - transaction boundary: N/A (read path)
- `POST /api/billing/finalize` -> `api_billing_finalize`
  - uses `exec_txn(...)` for finalize writes
  - duplicate/error branch also uses txn wrappers

## Idempotency Evidence
- In finalize handler, same-month output tables are delete+replace style before insert.
- State: **Proven at table replacement level** for targeted billing output tables.

## Proof Status
- Transaction boundary: **Closed (Proven)**
- Rollback semantics: **Closed (Proven)**
- Idempotent finalize rerun semantics: **Closed (Proven at scoped table level)**
