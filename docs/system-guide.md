# SIPR Laravel System Guide

## 1. What This System Is

SIPR is a Laravel-based member management and operations platform built around a cooperative-style financial workflow. The current codebase centers on:

- member access and role-based permissions,
- monthly member savings and investment entries,
- transactions and wallet balances,
- investment tracking with milestones and collection entries,
- a noticeboard for announcements and proposals,
- document storage and downloads,
- activity logging,
- reporting and asynchronous export generation,
- admin approval and access-control management.

The app is not a generic Laravel starter anymore. It is a domain application with a fairly strict authorization layer, a handful of core services, and a Blade/Tailwind UI.

## 2. Runtime And Stack

The project uses:

- Laravel 12
- PHP 8.2
- MySQL as the intended primary database
- Blade views with Tailwind CSS
- Laravel Breeze for auth scaffolding in dependencies
- Laravel Socialite in dependencies
- DomPDF for PDF generation
- queued jobs for report exports

The main startup wiring lives in `bootstrap/app.php`, where the route file is registered and the custom middleware aliases are attached:

- `active` -> `EnsureUserIsActive`
- `role` -> `EnsureUserHasRole`
- `option` -> `EnsureUserCanAccessOption`

`AppServiceProvider` registers the policies and the custom gates used by the app.

## 3. Request Flow At A High Level

The application follows a simple layered flow:

1. Routes are declared in `routes/web.php`.
2. Middleware filters access by authentication, active status, role, and role-options.
3. Controllers load models, validate requests, authorize with policies or gates, then call services when state changes need to stay consistent.
4. Services handle cross-model behavior such as wallet recalculation, proposal finalization, or activity logging.
5. Notifications and queued jobs handle asynchronous user feedback and long-running exports.

That separation is strongest in transactions, reporting, and proposal governance.

## 4. Routing And Entry Points

### Public and auth routes

The app redirects `/` to `/dashboard`. Guest routes include:

- login
- register
- forgot password
- reset password

### Authenticated routes

Authenticated users who also pass the `active` middleware can access:

- dashboard
- profile and password updates
- activity log
- transactions
- wallets
- members
- investments
- noticeboard and proposal detail pages
- documents and downloads
- notifications
- reports and export requests
- module placeholder pages
- logout

### Admin routes

A separate `admin` prefix is protected by `auth`, `active`, and `role:admin`. It contains:

- pending approvals
- access control management
- role-to-option assignment

### Module shell route

`/modules/{module}` is a controlled placeholder route that returns a generic module shell view for a small allowlist of module names. This is useful as a staging surface, but it is not a real business module implementation by itself.

## 5. Access Control Model

The app uses three layers of access control.

### Status-based gatekeeping

`EnsureUserIsActive` blocks access for users whose `status` is not `active`. It logs the user out and redirects them to login with a message.

### Role-based restrictions

`EnsureUserHasRole` checks the authenticated user against a list of allowed role strings. It is used for direct route groups like `role:admin,finance,secretary`.

The role enum is defined in `app/Enums/Role.php` and currently includes:

- admin
- finance
- secretary
- advisor
- member

### Option-based authorization

`EnsureUserCanAccessOption` checks a named option such as `transactions`, `reports`, or `access_control`. It delegates to `App\Support\RoleAccess`.

`RoleAccess` defines:

- the full option catalog,
- default enabled options per role,
- the role-to-option normalization rules,
- the database-backed override lookup through `role_option_permissions`.

This means route access is not only role-based; it can also be toggled per role and feature area.

### Policy and gate layer

`AppServiceProvider` registers policies for:

- transactions
- users / members
- investments
- announcements
- proposals
- documents
- wallets

It also defines two gates:

- `viewReports`
- `viewActivityLog`

Those gates are driven by the role-option system, not hardcoded roles.

## 6. Core Domain Areas

### 6.1 Authentication And Onboarding

Implemented auth controllers cover:

- login
- logout
- registration
- password reset

Login blocks users whose status is not active.

The codebase also has a pending-approval admin workflow. The current app logic is split between registration, user status management, and the `pending_approvals` table.

Important note: the test suite references invite-code registration and Google Socialite login flows, but those features are not visible in the current controllers or route definitions. Treat them as gaps or work-in-progress unless you confirm they exist elsewhere in the repository.

### 6.2 Members

Members are managed through `MemberController` and `MemberPolicy`.

Behavior:

- active users can view the members directory,
- only admins can update a member record,
- member listing supports role, status, and search filters,
- admin edits can change role, status, lock state, title, and phone,
- member updates are written to the activity log.

The `User` model holds the core member fields including:

- member_id
- name
- email
- password
- phone
- title
- role
- locked
- address
- status
- photo_path

The user model also exposes relationships to transactions, wallet, proposals, votes, announcements, documents, activities, and pending approvals.

### 6.3 Transactions

Transactions are one of the most important domain objects.

A transaction contains:

- type
- user_id
- amount
- date
- note

Types currently allowed by validation and schema are:

- deposit
- investment
- profit
- expense
- fine

`TransactionController` supports:

- list view with filters by type, member, and date range,
- detail view,
- create, update, and delete for privileged roles.

The critical business logic is delegated to `WalletService`.

Wallet behavior:

- deposit and profit increase the wallet,
- investment, expense, and fine decrease the wallet,
- each create/update/delete writes a wallet history entry,
- wallet rows are auto-created if missing.

This is the clearest example of state consistency in the app.

### 6.3.1 Monthly Savings/Investment Business Flow (Your Core Requirement)

The system supports the exact workflow where members contribute money monthly and then see their running totals.

Operational flow:

1. A privileged officer enters money against a member account.
2. The entry is stored as a `transactions` row under that member (`user_id`).
3. `WalletService` immediately recalculates the member wallet balance.
4. A `wallet_histories` row is written so there is a visible passbook-style ledger.
5. The member can open Wallet/Transactions pages and see current totals and recent entries.

How this maps to your role wording:

- "Admin" is implemented as role `admin`.
- "Treasurer" is currently implemented as role `finance` in this codebase.

Typical monthly entries:

- monthly savings can be entered as `deposit`,
- monthly member investment contributions can be entered as `investment`.

Balance effect rule:

- `deposit` increases member savings balance,
- `investment` decreases available wallet balance (treated as money allocated from available funds).

If you want both "savings total" and "investment total" shown as separate cumulative numbers in the UI, that is a presentation/reporting enhancement on top of existing transaction data.

### 6.4 Wallets

Wallets are read-only from the controller side in the current codebase.

Each wallet tracks:

- available balance
- locked balance

Wallet history entries track:

- date
- type
- label
- amount
- note
- lock state
- optional unlock date

Privilege summary:

- active users can view wallet data,
- admins, finance, secretary, and advisor can see all wallets,
- normal members are scoped to their own wallet.

### 6.5 Investments

Investments are managed through `InvestmentController` and `InvestmentPolicy`.

Investment fields include:

- name
- description
- sector
- partner
- date
- capital_deployed
- expected_return
- actual_return
- status
- team_members
- notes

Supported statuses are:

- active
- completed
- paused

Investments have two child models:

- milestones
- collections

Milestones track project steps and completion state. Collections track operational or revenue activity with profit calculation.

Privileged roles can:

- create investments,
- update investments,
- delete investments,
- add milestones,
- update milestones,
- add collection entries,
- update collection entries.

Each investment action is logged.

### 6.6 Noticeboard

The noticeboard combines announcements and proposals.

Announcements are simple posts with:

- title
- message
- author_id
- pinned

Only admin and secretary can create, update, or delete announcements.

Proposals are richer and include governance behavior:

- title
- description
- amount
- date
- proposed_by
- status
- quorum_required
- closes_at
- finalized_at

Proposal flow:

- any active user can create a proposal,
- active users can vote yes/no,
- finance, secretary, and admin can update the status,
- finance, secretary, and admin can finalize proposals.

`ProposalGovernanceService` handles finalization rules:

- a proposal can finalize after quorum is met or the close date is reached,
- yes/no counts are loaded before finalization,
- if quorum is reached and yes > no, the status becomes approved,
- otherwise it becomes rejected,
- finalization writes an activity record,
- affected users receive a notification.

### 6.7 Documents

Documents can be either:

- a stored file in public storage, or
- an external URL.

Document fields:

- name
- category
- url
- file_path
- uploaded_by

The current categories are:

- meeting-notes
- financial-report
- legal
- research
- photo
- other

Document flow:

- active users can browse and view documents,
- privileged users can upload new documents,
- admin and secretary can delete documents,
- downloads either stream the stored file or redirect to the external URL.

Uploads are stored under `storage/app/public/documents` through the public disk, and deletes clean up the stored file.

### 6.8 Activity Log

Activity tracking is centralized in `ActivityLogService`.

It records action codes such as:

- tx-create
- tx-update
- tx-delete
- member-update
- investment-create
- investment-update
- investment-delete
- investment-milestone-add
- investment-collection-add
- announcement-create
- announcement-update
- announcement-delete
- proposal-create
- proposal-update
- proposal-status
- proposal-vote
- proposal-finalized
- document-create
- document-delete

The activity list is filterable and only visible through the `viewActivityLog` gate.

## 7. Reporting And Async Work

Reports are one of the most complete subsystems in the app.

### Immediate exports

`ReportController` can stream:

- transactions CSV
- investments CSV
- wallet passbook PDF

### Asynchronous exports

The app also supports queued export generation through `ReportExport` and `GenerateReportExportJob`.

The job can create:

- transaction CSV exports,
- investment CSV exports,
- wallet passbook PDFs.

Lifecycle:

1. A privileged user requests an export.
2. A `report_exports` row is created with `pending` status.
3. The job updates it to `processing`.
4. The file is generated in public storage.
5. The record is marked `completed` or `failed`.
6. The requester receives a notification.

This is one of the few parts of the system that is explicitly asynchronous and user-notification driven.

## 8. Notifications

The app uses database and mail notifications for two main events:

- proposal finalization
- report export readiness

There is also a generic `InAppNotification` class for database-only notifications.

The notification inbox page reads from Laravel's built-in notifications relation and allows:

- listing notifications,
- marking one as read,
- marking all as read.

## 9. Data And Schema Shape

The schema is organized around a few core tables:

- users
- wallets
- wallet_histories
- transactions
- investments
- collections
- milestones
- announcements
- proposals
- proposal_votes
- documents
- activities
- pending_approvals
- notifications
- report_exports
- role_option_permissions

Notable implementation details:

- wallets are one-to-one with users,
- transactions and documents cascade on user deletion,
- role option permissions are unique per role/option pair,
- report exports store JSON filters and a completed timestamp,
- proposals have governance-related fields beyond the original baseline schema.

## 10. Frontend And Presentation Layer

The frontend is Blade-first.

Key points:

- `resources/views/layouts/app.blade.php` defines the main shell,
- the shell uses a dark, layered visual style with custom CSS variables,
- typography uses Manrope and Sora,
- the layout includes a fixed sidebar and a responsive content area,
- `resources/css/app.css` is a Tailwind entry point,
- `resources/js/app.js` currently only imports the bootstrap setup.

The UI is intentional rather than generic: glassy surfaces, gradients, and strong contrast are used throughout the main app shell.

## 11. Testing Coverage

The test suite is relatively strong for a Laravel app of this size. It covers:

- auth flows,
- approval flows,
- transactions and wallet effects,
- investment management,
- noticeboard actions,
- document upload and deletion,
- member updates,
- report exports,
- proposal governance,
- policy authorization,
- notification inbox behavior,
- missing-feature smoke tests.

That said, the tests also reveal some implementation drift:

- Google Socialite login is referenced in tests, but the current app code does not show the corresponding routes or controller methods.
- Invite-code registration is referenced in tests, but the visible registration controller is a simpler email/password flow.

Those are worth reconciling before treating the tests as a perfect map of production behavior.

## 12. Practical Mental Model

If you need to extend the app, think in this order:

1. Decide whether the feature is role-based, option-based, or both.
2. Add or update the policy/gate first.
3. Validate input in a FormRequest.
4. Keep state-changing business logic in a service if it affects more than one model.
5. Log important actions through `ActivityLogService`.
6. Notify users when the action is asynchronous or governance-related.
7. Add or update a feature test that proves the full request flow.

## 13. Current Gaps And Watchouts

A few things to watch closely:

- auth and onboarding code in the app does not yet match all test expectations,
- role-option permissions can override the default role matrix, so assume the database may change access behavior at runtime,
- the wallet update path depends on transaction amounts and types staying valid,
- proposal finalization has both manual and automatic paths, so status transitions need care,
- report exports depend on queue workers and public storage being available,
- document uploads depend on the public disk and storage linking,
- some controllers assume relationships exist and will create them on demand, especially wallets.

## 14. Suggested Next Reading Order

If you want to understand the system quickly, read these in order:

1. `routes/web.php`
2. `app/Providers/AppServiceProvider.php`
3. `app/Support/RoleAccess.php`
4. `app/Services/WalletService.php`
5. `app/Services/ProposalGovernanceService.php`
6. `app/Services/ActivityLogService.php`
7. `app/Http/Controllers/ReportController.php`
8. `app/Http/Controllers/NoticeboardController.php`
9. `app/Http/Controllers/InvestmentController.php`
10. `tests/Feature/*.php`

That sequence gives you the route map, the permission model, the core business logic, and then the expected behavior.

## 15. Feature Inclusion Analysis For Monthly Member Contribution Idea

This section answers: what should be included now, what can be included with controlled expansion, and what should not be included yet.

### 15.1 Features That Can Be Included Now (Already Supported)

These are directly aligned with your idea and already fit current architecture.

- Monthly savings/investment entry by privileged officers (admin/finance).
- Entry against a specific member account by member name or member ID.
- Member visibility of own balances and transaction history.
- Role-based protection so normal members cannot post money entries.
- Full audit trail through activity log and wallet history.
- Exportable reports (CSV/PDF) for finance/admin oversight.

Reason: these map to existing transaction, wallet, policy, and report flows.

### 15.2 Features That Can Be Included With Small To Medium Changes

These are good next-phase improvements that are low-risk and high-value.

- Separate dashboard totals per member:
    - lifetime total savings (sum of deposits),
    - lifetime total invested (sum of investment entries),
    - current available balance.
- Monthly contribution status tracking:
    - paid/unpaid per member per month,
    - arrears counter and reminders.
- Treasurer naming alignment in UI:
    - keep backend role as `finance`,
    - display label as "Treasurer" in interface.
- Transaction reason presets:
    - monthly subscription,
    - extra savings,
    - investment contribution,
    - penalty/fine.
- Safer correction workflow:
    - reversible adjustment entries instead of hard deletion.

Reason: all depend on existing models and services; they mostly require UI/reporting additions and a few schema extensions.

### 15.3 Features That Should Not Be Included Yet (Or Need Major Redesign)

These can create accounting/audit risk or exceed current architecture scope.

- Letting members directly edit wallet totals or transaction rows.
- Silent transaction deletion without correction trace.
- Profit-sharing engine with automatic weighted distribution across members, without a formal accounting module.
- Multi-currency accounting and FX conversion.
- Loan management (EMI, interest accrual, delinquency engine) inside the same wallet flow.
- Cross-organization tenancy in one database without tenant isolation design.

Reason: these require stronger accounting controls, stricter event-sourcing/audit design, or larger domain boundaries than the current app.

### 15.4 Suggested Inclusion Boundary (Recommended Scope)

Recommended product boundary for this phase:

1. Keep transactions as the source of truth for money movement.
2. Restrict money-entry actions to admin/finance only.
3. Show members read-only totals and history.
4. Add monthly due-status and separate savings/investment totals.
5. Preserve append-only financial audit behavior.

This gives a practical, reliable system for monthly member contributions without overcomplicating governance and finance logic.

## 16. Release Roadmap (3 Phases With Effort Estimates)

Effort legend used below:

- S: 1-2 developer days
- M: 3-5 developer days
- L: 6-10 developer days
- XL: 2+ developer weeks

Assumption: one full-stack Laravel developer with support for testing and deployment.

### Phase 1: Core Member Contribution Reliability (2-3 weeks)

Goal: make monthly savings/investment recording and member visibility fully reliable and clear.

Features:

1. Member totals card (Savings, Invested, Available): M
2. Member wallet page improvements (clear monthly history grouping): M
3. Treasurer UI labeling (display `finance` as Treasurer): S
4. Transaction reason presets for monthly operations: S
5. Financial correction pattern using adjustment entries (no silent delete): L
6. Feature tests for totals and adjustment behavior: M

Expected outcome:

- Officers can post monthly entries quickly.
- Members can reliably see personal totals.
- Audit trail remains intact for corrections.

### Phase 2: Monthly Due Tracking And Oversight (2-4 weeks)

Goal: track who paid each month, who did not, and what is pending.

Features:

1. Monthly dues table/model (member + month + expected + paid + status): L
2. Auto-mark dues status from transactions (paid/partial/unpaid): L
3. Admin/treasurer monthly collection dashboard: M
4. Per-member arrears counter and due summary: M
5. CSV export for monthly dues report: M
6. Notifications/reminders for unpaid members (in-app first, mail optional): M
7. Policy/option controls for dues management pages: S

Expected outcome:

- Team can monitor collections by month.
- Members can see due status transparently.
- Admin gets a clean arrears view.

### Phase 3: Governance And Scale Hardening (3-6 weeks)

Goal: make financial operations safer at scale and easier to govern.

Features:

1. Approval workflow for high-value transaction edits/adjustments: L
2. Period closing controls (lock previous month after reconciliation): L
3. Advanced reporting pack (member statement, monthly summary, arrears trend): L
4. Queue and storage operational hardening (retry, failure visibility, cleanup): M
5. Performance/index review for transaction and dues queries: M
6. Role-option review and least-privilege cleanup for financial actions: M
7. Regression test expansion for month-close and approval paths: M

Expected outcome:

- Financial operations become safer for larger groups.
- Month-end accounting is controlled and auditable.
- Reporting is decision-ready for leadership.

### Suggested Delivery Order Inside Each Phase

1. Schema and model updates
2. Authorization and policy updates
3. Service-layer business logic
4. Controller and UI integration
5. Tests and reporting validation

### Not Planned In This Roadmap

These are intentionally excluded from current 3-phase planning:

- multi-currency ledger,
- loan/EMI engine,
- fully automated profit-sharing accounting,
- multi-tenant architecture.

They should be treated as a separate roadmap after core monthly contribution operations are stable.

## 17. Sprint-by-Sprint Execution Plan (Actionable Tickets)

Assumption: 2-week sprints, one primary Laravel developer, one reviewer/tester.

### Sprint 1: Member Totals Foundation

Objective: expose clear member financial totals without changing core accounting behavior.

Tickets:

1. S1-T1: Add query/service method for member totals (deposits, investments, available)
    - Type: Backend
    - Estimate: 2 days
    - Acceptance:
        - totals are computed from transactions and wallet,
        - member-only scope is enforced for non-privileged users.

2. S1-T2: Add wallet summary cards in member wallet page
    - Type: Frontend (Blade)
    - Estimate: 2 days
    - Acceptance:
        - member sees Savings Total, Invested Total, Available Balance,
        - values match backend calculation for sample test data.

3. S1-T3: Add feature tests for totals visibility and role scoping
    - Type: Test
    - Estimate: 1 day
    - Acceptance:
        - member sees own totals only,
        - finance/admin can view all members when permitted.

4. S1-T4: Rename UI label from Finance to Treasurer (display only)
    - Type: Frontend
    - Estimate: 0.5 day
    - Acceptance:
        - UI text displays Treasurer,
        - backend role value remains `finance`.

Sprint deliverable:

- Members have a clear personal financial snapshot.

### Sprint 2: Monthly Entry Quality And Correction Safety

Objective: make monthly entry operations faster and safer.

Tickets:

1. S2-T1: Add transaction reason presets for monthly operations
    - Type: Frontend + Validation
    - Estimate: 1 day
    - Acceptance:
        - officers can choose standard reasons from dropdown,
        - free-text note still supported.

2. S2-T2: Add adjustment-entry flow for corrections
    - Type: Backend + UI
    - Estimate: 3 days
    - Acceptance:
        - correction creates a new reversing/adjustment transaction,
        - original transaction remains for audit,
        - wallet history reflects correction chain.

3. S2-T3: Restrict hard delete for posted transactions (or gate behind admin-only exceptional path)
    - Type: Policy + Controller
    - Estimate: 1 day
    - Acceptance:
        - normal correction is via adjustment,
        - unauthorized deletes are blocked.

4. S2-T4: Add activity log metadata for adjustments
    - Type: Backend
    - Estimate: 1 day
    - Acceptance:
        - log identifies original transaction and adjustment transaction IDs.

5. S2-T5: Add regression tests for correction workflow
    - Type: Test
    - Estimate: 1 day
    - Acceptance:
        - wallet balance after adjustment is correct,
        - audit logs are generated.

Sprint deliverable:

- Monthly posting is standardized, and corrections are traceable.

### Sprint 3: Monthly Due Tracking Data Model

Objective: introduce first-class due tracking per month.

Tickets:

1. S3-T1: Create monthly dues schema
    - Type: Database + Model
    - Estimate: 2 days
    - Acceptance:
        - due record stores member, month, expected amount, paid amount, status,
        - unique constraint prevents duplicate member-month rows.

2. S3-T2: Build due calculation service
    - Type: Backend Service
    - Estimate: 2 days
    - Acceptance:
        - service can compute paid/partial/unpaid from monthly transactions,
        - supports recalculation command for historical months.

3. S3-T3: Add monthly dues seeding/backfill command
    - Type: Console Command
    - Estimate: 1 day
    - Acceptance:
        - command generates dues for a target month and active members.

4. S3-T4: Add tests for dues status rules
    - Type: Test
    - Estimate: 1 day
    - Acceptance:
        - paid/partial/unpaid statuses are deterministic for edge cases.

Sprint deliverable:

- System has a reliable monthly dues backbone.

### Sprint 4: Dues Dashboard And Member Transparency

Objective: give admin/treasurer oversight and members clear due visibility.

Tickets:

1. S4-T1: Admin/Treasurer monthly collection dashboard
    - Type: Backend + UI
    - Estimate: 3 days
    - Acceptance:
        - dashboard shows total expected, total received, collection rate,
        - filter by month and role-permitted scope.

2. S4-T2: Member due status widget
    - Type: Frontend
    - Estimate: 1.5 days
    - Acceptance:
        - member sees current month status and arrears count.

3. S4-T3: CSV export for monthly dues
    - Type: Reporting
    - Estimate: 1.5 days
    - Acceptance:
        - exported file includes member ID, expected, paid, status, arrears.

4. S4-T4: Authorization integration for dues option
    - Type: Policy + Role Option
    - Estimate: 1 day
    - Acceptance:
        - new due pages are protected by option-level access.

5. S4-T5: Feature tests for dashboard and member due view
    - Type: Test
    - Estimate: 1 day
    - Acceptance:
        - role and option checks enforced,
        - numbers in UI match backend results.

Sprint deliverable:

- Monthly collections become operationally visible.

### Sprint 5: Approval Controls And Month Close

Objective: reduce operational risk on sensitive financial operations.

Tickets:

1. S5-T1: High-value adjustment approval workflow
    - Type: Backend + UI
    - Estimate: 3 days
    - Acceptance:
        - adjustments above threshold require admin approval,
        - pending/approved/rejected states are traceable.

2. S5-T2: Period close lock for previous months
    - Type: Backend Rule
    - Estimate: 2 days
    - Acceptance:
        - posting/editing in closed month is blocked unless privileged override.

3. S5-T3: Month-close audit report
    - Type: Reporting
    - Estimate: 1.5 days
    - Acceptance:
        - report includes totals, adjustments, and exceptions.

4. S5-T4: Policy and role-option review
    - Type: Security/Authorization
    - Estimate: 1 day
    - Acceptance:
        - least-privilege role map documented and enforced.

5. S5-T5: Regression tests for approval + month lock
    - Type: Test
    - Estimate: 1.5 days
    - Acceptance:
        - forbidden flows are blocked,
        - approved flows work end-to-end.

Sprint deliverable:

- Financial controls are safer for larger operational scale.

### Sprint 6: Hardening And Production Readiness

Objective: improve reliability, supportability, and launch confidence.

Tickets:

1. S6-T1: Queue reliability hardening for report/notification jobs
    - Type: Infrastructure + Backend
    - Estimate: 2 days
    - Acceptance:
        - retries, failure handling, and visibility are configured.

2. S6-T2: Performance tuning for key financial queries
    - Type: Database/Backend
    - Estimate: 2 days
    - Acceptance:
        - indexes validated for monthly dues and transaction lookups,
        - response times meet agreed thresholds.

3. S6-T3: UAT checklist and sign-off pack
    - Type: QA/Docs
    - Estimate: 1.5 days
    - Acceptance:
        - core business flows are mapped to UAT scenarios,
        - known limitations documented.

4. S6-T4: Deployment runbook update (queue, storage, backups)
    - Type: Ops Docs
    - Estimate: 1 day
    - Acceptance:
        - runbook includes rollback and incident checks.

Sprint deliverable:

- Platform is stable for production operation of monthly contribution workflows.

### Minimum Viable Cut (If Timeline Is Tight)

If you need fastest value, ship this cut first:

1. Sprint 1 fully
2. Sprint 2 tickets S2-T1, S2-T2, S2-T5
3. Sprint 3 tickets S3-T1, S3-T2
4. Sprint 4 tickets S4-T1, S4-T2

This delivers member totals, safer corrections, and monthly due visibility without waiting for full governance hardening.
