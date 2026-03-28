# CRITICAL_FINDINGS_REGISTER

| ID | Title | Severity | Component | Evidence | Impact | Recommended action |
|---|---|---|---|---|---|---|
| CF-001 | Laravel billing service depends on util billing tables not created by included migrations | Critical | Laravel DB contract | `DraftBillingFlowService.php` SQL refs to `util_billing_run/util_billing_line`; no matching migration files | Fresh deployment may fail runtime | Add/verify authoritative migrations for all util billing tables |
| CF-002 | Flask billing contract drift (`billing_run` vs `util_billing_run`) | Critical | Flask schema/engine | `unified_app/api/app.py:4337+` and `3485+` | Migration parity ambiguity; wrong-table writes risk | Normalize contract and publish authoritative schema map |
| CF-003 | Many Laravel operator pages are shell placeholders | High | Laravel UI modules | `ParityUiController.php` renderUiPage usage; `ui/page.blade.php` | False readiness signal for operations teams | Replace shells with real module pages or clearly gate/hide them |
| CF-004 | Removed flows (approve/adjustments/recovery) are 410 in both stacks | Medium | Billing governance | Flask `app.py:3593-3854`; Laravel `DraftBillingFlowService.php:560-597` | Business workflow differs from legacy expectations | Explicitly sign off governance policy and update SOPs |
| CF-005 | Docs contradict code on key readiness points | High | Release governance | `README.md`, `MISSING_FEATURE_MATRIX.md` vs routes/service code | Wrong go-live decision risk | Regenerate docs from code-backed audit only |
| CF-006 | Full non-EV1 billing parity not conclusively proven despite route coverage | High | End-to-end parity | Mixed engine/service/schema evidence | Cutover risk | Execute controlled golden-month E2E parity trial with fresh DB bootstrap |
