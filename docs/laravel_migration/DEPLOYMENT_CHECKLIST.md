# DEPLOYMENT_CHECKLIST

## Pre-Deployment
- [ ] Confirm branch/tag matches GO commit for core (`mbs_project` Laravel core).
- [ ] Verify runtime binaries on target host:
  - PHP (`C:\tools\php85\php.exe` or target equivalent)
  - Composer
- [ ] Verify environment file present and correct:
  - `APP_KEY` set
  - DB connection points to production DB
  - session/cache/log settings verified
- [ ] Ensure database backup snapshot is taken before migration.
- [ ] Run `composer install --no-dev --optimize-autoloader`.
- [ ] Run `php artisan config:cache` and `php artisan route:cache` (if deployment process supports it).

## Database & App Bring-up
- [ ] Put app in maintenance mode (if applicable).
- [ ] Run `php artisan migrate --force`.
- [ ] Run smoke checks:
  - auth login/logout
  - forced password change path
  - month guard blocking behavior
  - billing precheck/finalize/lock
  - reconciliation report + xlsx export
- [ ] Bring app out of maintenance mode.

## Post-Deployment Validation
- [ ] Run targeted health endpoint check.
- [ ] Validate logs for errors (auth, billing, reports).
- [ ] Execute one controlled reconciliation export and verify xlsx downloads.
- [ ] Confirm user role access matrix (SUPER_ADMIN/BILLING_ADMIN/DATA_ENTRY/VIEWER).

## Sign-off
- [ ] Capture deployed commit hash + release tag in ops log.
- [ ] Mark deployment complete with timestamp and operator.
