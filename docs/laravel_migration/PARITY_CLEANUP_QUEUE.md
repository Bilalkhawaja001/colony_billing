# PARITY_CLEANUP_QUEUE

## Completed in Validation Batch
- Runtime toolchain unblocked (PHP + Composer explicit binary flow)
- Laravel setup + migrations executed
- Full test suite green (44/44)
- Launch-critical runtime failures fixed:
  - JSON auth/role middleware behavior for non-`/api/*` JSON requests
  - test runtime DB isolation via `RefreshDatabase`
  - auth audit model timestamp mismatch
  - report export test/mock expectation alignment

## Remaining P0 Cleanup (pre-GO)
1. Replace `/export/excel/reconciliation` CSV adapter with true XLSX output parity.
2. Finalize compute internals from draft approximation to fully evidenced parity port.
3. Move month guard from shell/session config to domain month-state service.

## P1 Cleanup
- audit trail parity depth for all lock/finalize/report accesses
- response text exact-match pass vs Flask behavior

## Out of Scope (separate module)
- electric_v1 route/service parity
