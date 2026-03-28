# MISSING_FEATURE_MATRIX

| Module | Feature | Flask Reference | Laravel State | Severity | Status | Launch Impact |
|---|---|---|---|---|---|---|
| Billing | `/billing/run` with `run_key` idempotency | Present (`batchA app.py`) | Implemented (`/billing/run`) with idempotent run-key semantics | Critical | Closed | Resolved |
| Billing | Approve run (`/billing/approve`) | Present | Implemented and updates APPROVED status | Critical | Closed | Resolved |
| Billing | Lock run | Present | Present with extra month-state checks | Medium | Intentional change | Verify |
| Month | Open month (`/month/open`) writes cycle row | Present | Shell/pass response; write parity unclear | High | Partial | Blocking |
| Month | Transition month (`to_state`) | Present | Shell/exception pass route | High | Partial | Blocking |
| Rates | Upsert monthly rates | Present | Implemented (`/rates/upsert`) | Critical | Closed | Resolved |
| Rates | Approve monthly rates | Present | Implemented (`/rates/approve`) | Critical | Closed | Resolved |
| Imports | Mark batch validated | Present | Missing | High | Missing | High |
| Reports | Monthly summary JSON | Present | Present | Low | Partial parity | Non-blocking |
| Reports | Recovery JSON | Present | Present | Medium | Needs verification | Medium |
| Reports | Van JSON | Present | Present | Low | Partial parity | Non-blocking |
| Reports | Employee bill summary | Not in batchC API | Present (Laravel custom) | Medium | Needs verification | Medium |
| Reports | Elec summary | Not in batchC API | Present (Laravel custom) | Medium | Needs verification | Medium |
| Exports | Monthly summary CSV | Present | Missing | High | Missing | High |
| Exports | Monthly summary PDF | Present | Missing | High | Missing | High |
| Exports | Reconciliation XLSX | Not in batchC API | Present (Laravel custom) | Medium | Needs verification | Medium |
| Recovery | `/recovery/payment` active flow | Not in batchA; inferred from broader app variants | 410 disabled | Medium | Intentional change | Depends |
| Adjustments | Adjustment create/approve | Not in batchA; in broader historical app variants | 410 disabled | Medium | Intentional change | Depends |
| Auth | Login/reset/change-password parity | Not covered in batchA/C source | Implemented in Laravel draft | High | Needs verification | High |
| UI | Production operator frontend parity | Flask source in scope API-first; production UI not captured | Admin shell pages only | High | Partial | High |

## Severity totals
- Critical: 0
- High: 7
- Medium: 8
- Low: 1

## Notes
- Flask reference available in this audit is split service APIs (batchA + batchC), not full production UI bundle.
- Any module marked **Needs verification** requires comparison against the actual deployed Flask production codebase, not only batch proof APIs.
