# REMOVED_FLOW_POLICY_NOTE

## Policy (unchanged)
The following non-EV1 flows remain intentionally removed and must continue returning HTTP 410:
- `POST /billing/approve`
- `POST /billing/adjustments/create`
- `POST /billing/adjustments/approve`
- `POST /recovery/payment`

## Why
- Phase scope explicitly excludes reopening these flows.
- Flask reference behavior is already 410 on these paths.
- Avoid fake parity claims by pretending deprecated governance/recovery paths are active.

## Enforcement evidence
- Service methods return 410 in Laravel `DraftBillingFlowService`.
- Regression test aligned:
  - `tests/Feature/BillingLockApproveFlowTest::test_approve_flow_is_intentionally_removed_with_410`.

## Operator note
LIMITED GO path uses:
- run
- finalize
- lock
- reporting
- exports
and does **not** include removed flows above.