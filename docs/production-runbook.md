# SIPR Production Runbook

This runbook covers queue, storage, and report-export operational checks for production.

## 1. Pre-Deployment Checklist

- Verify environment values:
    - APP_ENV=production
    - APP_DEBUG=false
    - QUEUE_CONNECTION is set (database/redis)
    - FILESYSTEM_DISK=public
    - MAIL\_\* values set for notification delivery
- Run migrations:
    - php artisan migrate --force
- Rebuild caches:
    - php artisan config:cache
    - php artisan route:cache
    - php artisan view:cache

## 2. Queue Worker Operations

Start workers with restart support:

- php artisan queue:work --tries=3 --timeout=120

For supervisor-managed environments, ensure at least one worker process is always running.

Operational checks:

- Queue backlog health:
    - monitor pending jobs count and processing delay
- Failed jobs:
    - php artisan queue:failed
- Retry failed jobs after root-cause fix:
    - php artisan queue:retry all
- Clear failed jobs only when confirmed safe:
    - php artisan queue:flush

Deployment restart signal for workers:

- php artisan queue:restart

## 3. Report Export Storage Checks

Report exports are generated under public storage paths like:

- storage/app/public/reports/

Required checks:

- Storage symlink exists:
    - php artisan storage:link
- Public disk is writable by worker process user.
- Reports directory creation is successful during export job execution.

Post-deploy smoke test:

1. Request a transactions export from Reports page.
2. Confirm status transitions pending -> processing -> completed.
3. Confirm file download works.
4. Confirm notification is present in inbox.

## 4. Monthly Dues Operational Checks

Monthly close and reminders depend on data and permissions.

Checklist:

- Dues records exist for active members for target month.
- Monthly dues dashboard loads and KPIs are visible.
- CSV export for monthly dues downloads successfully.
- Reminder action sends notifications to partial/unpaid members only.
- Month close action is available to admin only.

## 5. Incident Playbook

### A) Export jobs stuck in pending

1. Check queue worker status.
2. Check failed jobs list.
3. Check app logs for job exceptions.
4. Retry failed jobs.
5. If storage error, fix permissions and retry.

### B) Export completed but file not downloadable

1. Verify file exists in storage/app/public/reports.
2. Verify storage symlink and web server file access.
3. Verify report_exports.file_path points to existing file.

### C) Notifications not showing

1. Verify notifications table has records.
2. Verify user account and auth context.
3. Verify reminder/report actions completed successfully.

### D) Finance cannot post transactions unexpectedly

1. Check month close status for transaction date.
2. Check role-option access for transactions/report modules.
3. Check role (finance/admin) and active status.

## 6. Backup And Recovery Notes

- Back up database regularly (includes transactions, dues, approvals, notifications).
- Back up storage/app/public/reports if retention is required.
- Restore sequence:
    1. Restore database.
    2. Restore public report files.
    3. Run integrity checks on report_exports and monthly_dues tables.

## 7. Recommended Monitoring Signals

- Queue pending job count and oldest job age.
- Failed jobs count.
- Report export completion rate.
- Number of unpaid/partial dues by month.
- Number of pending high-value adjustment approvals.

## 8. Release Verification (Post-Deploy)

1. Login as finance and create normal adjustment: should apply immediately.
2. Login as finance and create high-value adjustment: should become pending.
3. Login as admin and approve pending adjustment: wallet should update.
4. Close current month as admin and verify finance posting is blocked for that month.
5. Open monthly dues dashboard and export CSV.
6. Send unpaid reminders and verify inbox notifications.
