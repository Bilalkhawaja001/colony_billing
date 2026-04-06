# PHASE6_GOLDEN_MONTH_RESULTS

## High-level
Golden-month run re-executed after Option-A patch.

## Matched checks (post patch)
- run endpoint success in both stacks
- persisted line count parity for GOLDEN-RUN: 3 vs 3
- persisted line type parity: `SCHOOL_VAN`, `WATER_DRINKING`, `WATER_GENERAL`
- lock path works and run_status becomes LOCKED
- locked month write blocked
- finalize endpoint returns success on both

## Remaining observed variance
- `phase6_compare.py` still shows `db.finalize_rows` mismatch (`1 != 0`) because probe reads physical `billing_rows` table count, while Laravel finalize path returns success/rows without populating that legacy table in the same way as Flask probe setup.
- This variance is downstream of probe metric design, not Diff-001.

## Raw persisted lines (GOLDEN-RUN)
Source: `reports/phase6_line_dump.json`

### Flask persisted lines
- SCHOOL_VAN: 100
- WATER_DRINKING: 10
- WATER_GENERAL: 20
- Count: 3

### Laravel persisted lines
- SCHOOL_VAN: 100
- WATER_DRINKING: 10
- WATER_GENERAL: 20
- Count: 3

## Result for target mismatch
**Diff-001 closed** (ELEC over-retention removed).