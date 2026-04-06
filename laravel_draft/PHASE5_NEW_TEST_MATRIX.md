# PHASE5_NEW_TEST_MATRIX

| Test file | Coverage | Scope | Result |
|---|---|---|---|
| `Phase5FreshBootstrapSafetyTest` | table ownership on fresh DB | non-EV1 run/finalize/report/lock dependencies | Pass |
| `Phase5FreshBootstrapSafetyTest` | end-to-end limited workflow boot | run, summary, lock, locked-write-block, finalize | Pass |
| `Phase5UiShellVsWorkflowTruthTest` | shell render vs real workflow contract | `/ui/billing` render + `/billing/run` contract failure path | Pass |
| `Phase5RemovedFlowPolicyTest` | intentional deprecation lock | `/billing/approve`, `/billing/adjustments/*`, `/recovery/payment` => 410 | Pass |
| existing `BillingRunRatesParityTest` | run/rates + month guards | month existence/lock + run output contract | Pass |
| existing `BillingFinalizeFlowTest` | finalize semantics | rerun/idempotent behaviors | Pass |
| existing `BillingLockApproveFlowTest` | lock and removed approve policy | lock=200, approve=410 | Pass |
| existing `ReportsExportsActiveTest` | report/export path | summary/reconciliation/excel/pdf | Pass |
| existing `ImportsMonthlyRatesParityTest` | rates/import dependency path | monthly rates + mark validated + lock guards | Pass |
| existing `MonthGuardShellTest` | guard middleware behavior | locked month write blocking and exceptions | Pass |