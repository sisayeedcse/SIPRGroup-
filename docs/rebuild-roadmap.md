# SIPR Laravel Rebuild Roadmap (Laravel + MySQL)

## 1. Foundation (completed in this pass)
- MySQL-first env example.
- Clean migration history (removed empty duplicate migration stubs).
- Domain model relationships and casts.
- Role enum and role middleware alias.
- Structured module routing and starter module views/controllers.

## 2. Authentication and Authorization
- Install and configure Laravel Breeze (Blade + auth scaffolding).
- Add Google Socialite login flow and invite-code linking flow.
- Add approval workflow for pending members.
- Enforce role-based access using policies and `role` middleware.

## 3. Core Module Implementation
- Transactions: CRUD, filters, and monthly payment status logic.
- Investments: CRUD, collection records, milestones.
- Members: profile management, roles, fine workflow.
- Wallet: balances, locked entries, history, passbook data.
- Noticeboard: announcements, proposals, voting.
- Documents: upload (filesystem), category, links.
- Activity log: audit records for important actions.

## 4. Admin and Reporting
- Control panel for roles and approvals.
- CSV exports for financial records.
- PDF passbook/report generation via DomPDF.

## 5. Quality and Deployment
- Feature tests for each module.
- Policy tests for access control.
- Seeders for baseline SIPR members.
- Performance indexing and query review.
- Production hardening (.env, queue, cache, backups).

## Suggested Sprint Order
1. Auth + Approval + Roles
2. Transactions + Members + Wallet
3. Investments + Collections + Milestones
4. Noticeboard + Documents + Activity
5. Reports + Tests + Production readiness
