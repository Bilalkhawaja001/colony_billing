# ROLLBACK_CHECKLIST

## Rollback Trigger Conditions
- [ ] Migration failure with unrecoverable error.
- [ ] Critical auth failure (cannot log in / role gate broken).
- [ ] Critical billing core flow failure (precheck/finalize/lock) in production.
- [ ] Reconciliation/export endpoint failure affecting operations.

## Immediate Actions
- [ ] Enable maintenance mode.
- [ ] Stop new write operations to avoid divergence.
- [ ] Record incident timestamp + failing commit/tag.

## Technical Rollback
- [ ] Checkout previous stable release tag.
- [ ] Restore previous dependency lock state (`composer install`).
- [ ] Run rollback migration plan:
  - If safe: `php artisan migrate:rollback --step=<N>`
  - If not safe: restore DB from pre-deploy snapshot.
- [ ] Restore previous `.env` if config changed.

## Validation After Rollback
- [ ] Verify auth login/logout and role gates.
- [ ] Verify month guard behavior.
- [ ] Verify billing precheck/finalize/lock on known-safe test data.
- [ ] Verify reconciliation report + export endpoint.
- [ ] Confirm logs stabilized.

## Closure
- [ ] Announce rollback completion.
- [ ] Open root-cause ticket with failing commit/tag.
- [ ] Freeze further deploys until fix validated in staging.
