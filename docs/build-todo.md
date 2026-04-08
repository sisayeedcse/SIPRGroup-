# SIPR Build TODO

This file is the implementation checklist for the monthly member savings and investment system.

## Phase 1: Core Financial Visibility

- [x] Add member financial summary service (savings total, invested total, available balance).
- [x] Show summary cards on wallet passbook page.
- [x] Ensure role-scoped visibility (member sees own, admin/treasurer can view selected member).
- [x] Add/extend feature tests for financial summary accuracy.
- [x] Add activity labels and UI wording consistency (Treasurer label).

## Phase 2: Monthly Due Tracking

- [x] Create monthly dues table and model.
- [x] Create due status engine (paid, partial, unpaid).
- [x] Add admin/treasurer monthly dashboard.
- [x] Add member due status widget and arrears count.
- [x] Add monthly dues CSV report.
- [x] Add notification reminders for unpaid members.

## Phase 3: Governance And Reliability

- [x] Add adjustment-based correction workflow (append-only accounting behavior).
- [x] Add high-value adjustment approval flow.
- [x] Add month close lock rules.
- [x] Expand test coverage for month-close and approvals.
- [x] Add production runbook checks for queue/storage/report jobs.

## In Progress Now

- [x] Implement Phase 1 financial summary service and wallet UI integration.
