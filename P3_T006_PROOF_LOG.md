# P3_T006_PROOF_LOG

## Commands executed
1. `C:\tools\php85\php.exe artisan migrate:fresh --force`
2. `C:\tools\php85\php.exe vendor\bin\phpunit --filter P3T006IntegratedRerunIdempotencyTest`

## Test result
- Suite: `Tests\Feature\P3T006IntegratedRerunIdempotencyTest`
- Status: PASS
- Tests: 1
- Assertions: 30
- Failures: 0

## Proof checkpoints
- Integrated month/input/water/van/family/run/report chain executed
- Same run_key rerun kept same run_id
- key dataset/report hashes stable across rerun
- no rowcount drift on run lines/finalized rows
- export endpoints still operational
- EV1 untouched
