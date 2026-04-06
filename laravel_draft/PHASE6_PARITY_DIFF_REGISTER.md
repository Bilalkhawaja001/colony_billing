# PHASE6_PARITY_DIFF_REGISTER

## Diff-001
- expected = 3
- actual (before patch) = 4
- extra line type = ELEC
- first divergence point = DB write stage
- severity = **Blocker**
- status = **Closed**

### Evidence (after patch)
- Flask raw lines: `reports/phase6_line_dump.json` -> `flask` array count `3`
- Laravel raw lines: `reports/phase6_line_dump.json` -> `laravel` array count `3`
- Final line types both sides: `SCHOOL_VAN`, `WATER_DRINKING`, `WATER_GENERAL`
- ELEC absent in final persisted set on Laravel as required.

### Minimal patch applied
- File: `laravel_draft/app/Services/Billing/DraftBillingFlowService.php`
- Change: moved `DELETE FROM util_billing_line WHERE billing_run_id=?` to execute **after** ELEC insert and before water/drinking/van inserts, matching Flask sequence.

### Regression lock
- Added: `laravel_draft/tests/Feature/Phase6ElecPersistenceParityTest.php`
- Asserts:
  - final persisted line count = 3
  - exact line types = `SCHOOL_VAN`, `WATER_DRINKING`, `WATER_GENERAL`
  - ELEC not present.