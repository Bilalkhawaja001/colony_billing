# MODULE_PAGES_AUDIT

## Classification key
- **Real**: page has dedicated view + meaningful backend integration.
- **Shell**: generic placeholder wrapper.
- **Partial**: some real data but not full workflow-ready.

| Module page | Flask | Laravel | Status | Evidence |
|---|---|---|---|---|
| `/ui/dashboard` | Real | Real (data-backed) | Partial parity | Flask `app.py:4481`; Laravel `ParityUiController::dashboard` |
| `/ui/reports` | Real | Real-ish summary page | Partial parity | `app.py:1355`; `ParityUiController::reports` |
| `/ui/reconciliation` | Real | Real-ish | Partial parity | `app.py:1360`; `ParityUiController::reconciliation` |
| `/ui/billing` | Real billing module | Generic shell | **Gap** | `app.py:1350`; `ParityUiController.php:81-83`; `ui/page.blade.php:6` |
| `/ui/month-cycle` | Real | Shell | **Gap** | `app.py:1317`; `ParityUiController.php:71-73` |
| `/ui/rates` | Real | Shell | Gap | Flask route + template; Laravel renderUiPage |
| `/ui/imports` | Real | Shell | Gap | Flask route + template; Laravel renderUiPage |
| `/ui/water-meters` | Real | Shell | Gap | same pattern |
| `/ui/van` | Real | Shell | Gap | same pattern |
| `/ui/employees` | Real | Shell | Gap | same pattern |
| `/ui/unit-master` | Real | Shell | Gap | same pattern |
| `/ui/meter-master` | Real | Shell | Gap | same pattern |
| `/ui/rooms` | Real | Shell | Gap | same pattern |
| `/ui/occupancy` | Real | Shell | Gap | same pattern |
| `/ui/electric-v1-run` | Real | Dedicated EV1 view | Better parity | Flask + Laravel EV1 routes/views |
| `/ui/electric-v1-outputs` | Real | Dedicated EV1 view | Better parity | same |
| `/ui/family-details`, results, logs | Real | Dedicated views exist | Partial parity | routes + view mappings |

## Key finding
Laravel has broad page coverage by URL, but many operator modules are **presentation shells** rather than full module pages. This is a release-risk because navigation suggests readiness while backend depth varies by module.