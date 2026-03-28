# MBS Different Route Exceptions (Intentional)

Scope: resolve all `Different` items from parity register by documenting intentional divergence with hard code-path evidence + tests.

## Evidence matrix

| Item (Flask) | Laravel route evidence | Runtime code-path evidence | Resolution | Test proof |
|---|---|---|---|---|
| `POST /billing/approve` (`billing_approve`) | `routes/web.php:L102` → `BillingDraftController::approve` | `app/Http/Controllers/Billing/BillingDraftController.php:L44` calls service; `app/Services/Billing/DraftBillingFlowService.php:L524-L531` returns `_http => 410`, `error: approval flow removed; use direct finalize flow` | **Intentional exception** (removed flow retained as explicit 410 parity behavior) | `tests/Feature/DifferentParityExceptionsTest.php::test_billing_approve_is_intentionally_removed_with_410` |
| `POST /billing/adjustments/create` (`billing_adjustment_create`) | `routes/web.php:L105` → `BillingDraftController::adjustmentCreate` | `BillingDraftController.php:L52` calls service; `DraftBillingFlowService.php:L534-L541` returns `_http => 410`, `error: deduction/adjustment flow removed; billing generation only` | **Intentional exception** (endpoint exists, behavior intentionally hard-disabled) | `tests/Feature/DifferentParityExceptionsTest.php::test_billing_adjustment_create_is_intentionally_removed_with_410` |
| `POST /billing/adjustments/approve` (`billing_adjustment_approve`) | `routes/web.php:L106` → `BillingDraftController::adjustmentApprove` | `BillingDraftController.php:L60` calls service; `DraftBillingFlowService.php:L544-L551` returns `_http => 410`, `error: adjustment approvals removed` | **Intentional exception** (approval path removed) | `tests/Feature/DifferentParityExceptionsTest.php::test_billing_adjustment_approve_is_intentionally_removed_with_410` |
| `POST /recovery/payment` (`recovery_payment_create`) | `routes/web.php:L107` → `BillingDraftController::recoveryPayment` | `BillingDraftController.php:L68` calls service; `DraftBillingFlowService.php:L554-L561` returns `_http => 410`, `error: payment receiving disabled; billing generation only` | **Intentional exception** (recovery payment intake removed from active flow) | `tests/Feature/DifferentParityExceptionsTest.php::test_recovery_payment_is_intentionally_removed_with_410` |

## Why these remain `Different`

`recompute_live_gap_register.py` marks these endpoints as `Different` by design when handler methods return HTTP 410 removed-flow behavior. This is expected and now explicitly documented.
