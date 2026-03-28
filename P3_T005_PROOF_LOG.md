# P3_T005_PROOF_LOG

## Commands executed
1. `C:\tools\php85\php.exe artisan migrate:fresh --force`
2. `C:\tools\php85\php.exe vendor\bin\phpunit --filter P3T005RoleGuardMatrixTest`

## Test result
- Suite: `Tests\Feature\P3T005RoleGuardMatrixTest`
- Status: PASS
- Tests: 1
- Assertions: 17
- Failures: 0

## Proof checkpoints
- Invalid transition/malformed month payload rejections (422)
- Role denial matrix for viewer on write endpoints (403)
- Locked-state write block on billing run (409)
- Locked-state month guard block on rates upsert (409 + guard code)
- EV1 untouched
