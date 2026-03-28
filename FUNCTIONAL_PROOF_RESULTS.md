# FUNCTIONAL_PROOF_RESULTS

## Proof method
Feature-level integration test executed against real endpoints with DB assertions and role checks.

## Commands executed
1. `C:\tools\php85\php.exe artisan migrate:fresh --force`
2. `C:\tools\php85\php.exe vendor\bin\phpunit --filter Phase3FunctionalGapClosureTest`

## Test suite
- `Tests\Feature\Phase3FunctionalGapClosureTest`
- Result: PASS
- Tests: 1
- Assertions: 37
- Failures: 0

## Verified proof points
- Rooms create + update + import-like batch write path
- Rooms validation failure path (invalid category)
- Rooms reload/list persistence
- Occupancy create + update + import-like batch write path
- Occupancy validation failure path (missing required fields)
- Occupancy reload/list persistence
- Inputs mapping operational action path (`/api/rooms/cascade`)
- Inputs readings upsert + latest lookup path
- Inputs RO allocation preview + water adjustments path
- Role/access checks (VIEWER denied on `/rooms/upsert`, `/occupancy/upsert`, `/api/water/zone-adjustments`)

## Notes
- PHPUnit reported deprecations (non-blocking), functional assertions all passed.
