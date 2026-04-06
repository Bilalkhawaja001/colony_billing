# Deployment Runbook (Mini Utility-ERP)

## Pre-Deploy
1. Backup DB file
2. Verify schema boot with `python -m py_compile api/app.py`
3. Confirm env:
   - `MBS_DB_PATH`
   - `MBS_ADMIN_USER_IDS`

## Deploy
1. Restart Flask service/app process
2. Open `/ui/monthly-setup?actor_user_id=<admin>`
3. Smoke checks:
   - `GET /health`
   - `GET /monthly-rates/history`
   - `GET /billing/fingerprint?month_cycle=<approved_or_locked_month>`

## Post-Deploy Controls
1. Ensure month transitions are admin-only
2. Ensure locked month mutation returns 409
3. Ensure audit rows are writing to `util_audit_log`

## Rollback (Checklist)
Use rollback if any deployment gate fails (health not 200, locked-month guard broken, or billing fingerprint endpoint unstable).

1. Stop service/process.
2. Restore previous DB backup file.
3. Restore previous `api/app.py` build (and related templates/static if changed in release).
4. Start service/process.
5. Verify recovery gates:
   - `GET /health` => 200
   - `GET /monthly-rates/history` => 200
   - `GET /billing/fingerprint?month_cycle=<approved_or_locked_month>` => 200
6. Confirm audit continuity in `util_audit_log` after rollback restart.
