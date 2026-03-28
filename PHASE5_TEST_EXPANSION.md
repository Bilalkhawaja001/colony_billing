# PHASE5_TEST_EXPANSION

## Objective
Fresh-bootstrap + non-EV1 regression protection close karna taake hidden dependencies catch ho jayein.

## New tests added
1. `laravel_draft/tests/Feature/Phase5FreshBootstrapSafetyTest.php`
   - verifies required non-EV1 tables exist after fresh bootstrap
   - verifies run -> report -> lock -> locked-write-block -> finalize chain boots on fresh DB
2. `laravel_draft/tests/Feature/Phase5UiShellVsWorkflowTruthTest.php`
   - proves shell page render != workflow completion
3. `laravel_draft/tests/Feature/Phase5RemovedFlowPolicyTest.php`
   - locks intentional 410 policy for removed flows

## Supporting hardening for test reliability
- `DraftBillingFlowService::run()`
  - explicit replace step `DELETE FROM util_billing_line WHERE billing_run_id=?`
  - sets run status `APPROVED` for report-open parity with Flask flow
- migration extended for finalize-path bootstrap tables:
  - `billing_run`, `hr_input`, `map_room`, `readings`, `ro_drinking`

## Phase 5 result
- Fresh bootstrap + targeted critical suite passed.
- Removed 410 behavior now regression-locked.
- Shell-vs-workflow false confidence now test-covered.