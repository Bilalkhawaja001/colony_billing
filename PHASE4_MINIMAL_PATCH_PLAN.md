# PHASE4_MINIMAL_PATCH_PLAN

## Scope lock honored
- EV1 untouched.
- No broad refactor.
- No re-enable of removed flows (`approve`, `adjustments`, `recovery payment`).

## P0 patch set (implemented)
1. **Run-path hard guard**
   - Normalize month_cycle input (`YYYY-MM` -> `MM-YYYY`)
   - Reject invalid format
   - Reject unknown month
   - Reject LOCKED month
   - Fail if run_id resolution fails

2. **Month guard coverage**
   - Add `/billing/run`, `/billing/elec/compute`, `/billing/water/compute` to protected write paths.

3. **Rates dependency hardening**
   - Month validation/normalization for rates upsert/approve
   - Return 404 when approve targets non-existent month rates row

4. **Validation hardening**
   - Billing run + rates requests accept strict month pattern and non-negative rates.

5. **Policy-aligned regression tests**
   - Approve remains explicit 410.
   - Billing run requires existing unlocked month.

## Exclusions (intentional)
- UI shell buildout not done in Phase 4.
- Removed 410 flows remain removed.
- EV1 code path untouched.