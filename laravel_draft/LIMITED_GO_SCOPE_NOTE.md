# LIMITED_GO_SCOPE_NOTE

## Intended LIMITED GO scope (non-EV1)
- billing run
- billing finalize
- month lock/guard path
- reporting
- exports
- rates/import dependency needed for above

## Explicit exclusions
- `/billing/approve` (410)
- `/billing/adjustments/create` (410)
- `/billing/adjustments/approve` (410)
- `/recovery/payment` (410)
- shell-only pages as proof of completeness

## Current gate status
LIMITED GO proven for constrained non-EV1 scope after Option-A parity patch and re-proof.

## Closed blocker
- Diff-001 (ELEC over-retention) is closed.
- Persisted GOLDEN-RUN lines now exactly mirror Flask authoritative set (3 lines, no ELEC).

## Constraints still active
- Full GO still forbidden.
- Removed 410 flows remain excluded.
- Shell modules remain excluded from workflow completeness claims.