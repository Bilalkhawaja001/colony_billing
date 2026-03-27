# Colony Billing (Laravel)

## Release Status (Verified)
- Branch `main` pushed to GitHub
- Verified commit baseline: `05622c1af3399a80395aa13b5912a2605e1c6c23`
- ElectricV1 regression gate: **green** (`16 tests, 212 assertions`)
- Full PHPUnit suite: **green** (latest verified in release run)

## Current Operational State
- Core auth/session/RBAC flows active
- ElectricV1 run/outputs APIs and UI routes active
- Billing/report/export/import/admin/master-data routes active in current release
- Legacy removed flows are intentionally retained as explicit 410 exceptions (see below)

## Approved/Documented Exceptions (Intentional)
The following endpoints intentionally return 410 (removed-flow behavior):
1. `POST /billing/approve`
2. `POST /billing/adjustments/create`
3. `POST /billing/adjustments/approve`
4. `POST /recovery/payment`

Reference tests: `tests/Feature/DifferentParityExceptionsTest.php`

## Utility Scripts
- This repo includes runtime/dev scripts under `scripts/`.
- `scripts/recompute_live_gap_register.py` is **not** part of this public release repo.
- Reason: parity recount tooling depends on private migration workspace layout and source-of-truth files that are outside this public repository.

## Quick Local Verification
```bash
php vendor/bin/phpunit --filter ElectricV1
php vendor/bin/phpunit
```

## UAT Entry
Use `UAT_CHECKLIST.md` for controlled UAT validation sequence.
