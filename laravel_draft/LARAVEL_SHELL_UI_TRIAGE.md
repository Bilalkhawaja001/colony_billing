# LARAVEL_SHELL_UI_TRIAGE

Scope requested:
- `/ui/billing`
- `/ui/month-cycle`
- `/ui/imports`
- `/ui/rates`

## Current status
All four routes are currently rendered through generic shell helper:
- `ParityUiController::renderUiPage(...)`
- Template: `resources/views/ui/page.blade.php` ("Parity draft page is active")

Evidence:
- `laravel_draft/app/Http/Controllers/Ui/ParityUiController.php:15-17, 71-83, 111`
- `laravel_draft/resources/views/ui/page.blade.php:6`

## Triage priority
1. `/ui/billing` — **P0** (core operations)
2. `/ui/month-cycle` — **P0** (state governance)
3. `/ui/imports` — **P1** (data pipeline)
4. `/ui/rates` — **P1** (pricing controls)

## Minimal completion criteria per page
- Dedicated blade view (not generic shell)
- Wired API actions mapped to existing controllers/services
- Role-aware action visibility
- Error state rendering for locked month / blocked routes
- Smoke test for page render + one real action

## Note
This triage file only classifies and gates work. No fake parity claim implied.