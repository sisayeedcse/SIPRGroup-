@extends('layouts.app')

@section('title', 'Dashboard | SIPR')
@section('pageTitle', 'Dashboard')
@section('pageSubtitle', 'A compact snapshot of what needs your attention right now.')

@section('content')
    <div class="page-stack">
        @php
            $userModel = \App\Models\User::class;
            $role = auth()->user()->role->value ?? auth()->user()->role;
            $isAdmin = $role === 'admin';
            $unreadCount = auth()->user()->unreadNotifications()->count();
            $lifetimeDeposited = (float) ($financialSummary['savings_total'] ?? 0);
            $lifetimeInvested = (float) ($financialSummary['invested_total'] ?? 0);
            $expected = (float) ($dueSnapshot['current_expected'] ?? 0);
            $paid = (float) ($dueSnapshot['current_paid'] ?? 0);
            $outstanding = max(0, $expected - $paid);
            $monthContribs = $monthlyMemberContributions ?? [];
            $membersForDeposit = $adminMembers ?? collect();
        @endphp

        <style>
            .compact-hero {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 90vw, 100%), 1fr));
                gap: clamp(10px, 2vw, 14px);
                align-items: stretch;
            }

            .compact-intro {
                border-radius: var(--radius-lg);
                border: 1px solid var(--line);
                background: linear-gradient(135deg, rgba(24, 39, 67, 0.86), rgba(11, 20, 36, 0.92));
                padding: clamp(14px, 3vw, 18px);
                box-shadow: var(--shadow-soft);
            }

            .compact-intro h2 {
                margin: 4px 0 8px;
                font-size: clamp(18px, 4vw, 24px);
                line-height: 1.2;
                word-break: break-word;
            }

            .compact-meta {
                display: flex;
                flex-wrap: wrap;
                gap: clamp(6px, 1vw, 8px);
                margin: 0 0 12px;
                color: var(--muted);
                font-size: clamp(11px, 1.5vw, 12px);
                font-weight: 700;
            }

            .status-pills {
                display: flex;
                gap: clamp(6px, 1vw, 8px);
                flex-wrap: wrap;
                margin-bottom: 12px;
            }

            .pill {
                border-radius: 999px;
                border: 1px solid var(--line);
                padding: clamp(4px, 1vw, 6px) clamp(8px, 2vw, 10px);
                font-size: clamp(10px, 1.5vw, 12px);
                font-weight: 800;
                letter-spacing: .03em;
                color: #d8e8ff;
                background: rgba(76, 169, 255, 0.1);
                white-space: nowrap;
                flex-shrink: 0;
            }

            .compact-actions {
                display: flex;
                gap: clamp(6px, 1vw, 8px);
                flex-wrap: wrap;
            }

            .action-chip {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                border: 1px solid var(--line);
                color: #def0ff;
                background: rgba(76, 169, 255, 0.12);
                padding: clamp(6px, 1vw, 8px) clamp(10px, 2vw, 12px);
                font-size: clamp(11px, 1.5vw, 12px);
                font-weight: 800;
                transition: .2s ease;
                min-height: 44px;
                cursor: pointer;
                flex: 1;
                min-width: clamp(80px, 40vw, 100%);
            }

            .action-chip:hover {
                background: rgba(76, 169, 255, 0.2);
                border-color: var(--line-strong);
            }

            .compact-focus {
                border-radius: var(--radius-lg);
                border: 1px solid var(--line);
                padding: clamp(12px, 3vw, 16px);
                background: rgba(15, 24, 44, 0.9);
                box-shadow: var(--shadow-soft);
            }

            .focus-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: clamp(8px, 2vw, 10px);
                margin-bottom: 10px;
                flex-wrap: wrap;
            }

            .focus-head h3 {
                margin: 0;
                font-size: clamp(12px, 2vw, 15px);
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #cae3ff;
                flex: 1;
            }

            .tone {
                border-radius: 999px;
                padding: clamp(3px, 1vw, 4px) clamp(8px, 2vw, 10px);
                font-size: clamp(10px, 1.5vw, 11px);
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: .05em;
                border: 1px solid var(--line);
                white-space: nowrap;
                flex-shrink: 0;
            }

            .tone-ok {
                color: #bdf5df;
                background: rgba(62, 207, 142, 0.16);
                border-color: rgba(62, 207, 142, 0.4);
            }

            .tone-warn {
                color: #ffe1a8;
                background: rgba(247, 191, 77, 0.14);
                border-color: rgba(247, 191, 77, 0.38);
            }

            .tone-danger {
                color: #ffc4cc;
                background: rgba(255, 107, 127, 0.14);
                border-color: rgba(255, 107, 127, 0.42);
            }

            .mini-bar {
                width: 100%;
                height: 8px;
                border-radius: 999px;
                border: 1px solid var(--line);
                background: rgba(255, 255, 255, 0.06);
                overflow: hidden;
                margin: 10px 0 8px;
            }

            .mini-fill {
                height: 100%;
                border-radius: 999px;
                background: linear-gradient(90deg, #4ca9ff, #3ecf8e);
            }

            .focus-note {
                margin: 0;
                font-size: clamp(11px, 1.5vw, 12px);
                color: var(--muted);
                line-height: 1.45;
            }

            .smart-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(clamp(120px, 40vw, 100%), 1fr));
                gap: clamp(8px, 2vw, 10px);
            }

            .data-card {
                border-radius: 14px;
                border: 1px solid var(--line);
                padding: clamp(10px, 2vw, 12px);
                background: rgba(18, 27, 49, 0.7);
            }

            .data-card .label {
                margin-bottom: 4px;
            }

            .data-card .value {
                font-size: clamp(16px, 4vw, 20px);
                line-height: 1.2;
                font-weight: 700;
            }

            .accordion-stack {
                display: grid;
                gap: clamp(8px, 2vw, 10px);
                margin-top: 12px;
            }

            .admin-accordion {
                border: 1px solid var(--line);
                border-radius: 12px;
                overflow: hidden;
                background: rgba(16, 25, 44, 0.78);
            }

            .admin-accordion summary {
                list-style: none;
                cursor: pointer;
                display: grid;
                grid-template-columns: 1fr;
                gap: clamp(6px, 1vw, 10px);
                align-items: center;
                padding: clamp(10px, 2vw, 12px) clamp(10px, 2vw, 14px);
                font-weight: 800;
                color: #dbeaff;
                font-size: clamp(12px, 1.5vw, 13px);
                min-height: 44px;
            }

            .admin-accordion summary::-webkit-details-marker {
                display: none;
            }

            .acc-pill {
                font-size: clamp(10px, 1.5vw, 11px);
                padding: clamp(3px, 1vw, 4px) clamp(6px, 1vw, 8px);
                border-radius: 999px;
                border: 1px solid var(--line);
                color: #cbe4ff;
                background: rgba(76, 169, 255, 0.14);
                white-space: nowrap;
                flex-shrink: 0;
            }

            .acc-toggle {
                font-size: clamp(10px, 1.5vw, 11px);
                letter-spacing: .04em;
                text-transform: uppercase;
                color: var(--muted);
            }

            .admin-accordion[open] .acc-toggle::after {
                content: ' (hide)';
            }

            .admin-accordion:not([open]) .acc-toggle::after {
                content: ' (show)';
            }

            .accordion-body {
                border-top: 1px solid var(--line);
                padding: clamp(8px, 2vw, 10px);
                display: grid;
                gap: 8px;
            }

            .list-head,
            .list-row {
                display: grid;
                grid-template-columns: 1fr;
                gap: 4px;
                align-items: flex-start;
            }

            .list-head {
                font-size: clamp(10px, 1.5vw, 11px);
                text-transform: uppercase;
                letter-spacing: .06em;
                color: var(--muted);
                font-weight: 800;
                padding: 2px 6px;
            }

            .list-row {
                padding: clamp(8px, 2vw, 10px) clamp(10px, 2vw, 12px);
                border: 1px solid var(--line);
                border-radius: 10px;
                background: rgba(255, 255, 255, 0.02);
                font-size: clamp(12px, 1.5vw, 13px);
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                align-items: center;
            }

            .list-row>* {
                flex: 1;
                min-width: 120px;
            }

            .list-row .amount {
                text-align: left;
                font-weight: 800;
                color: #cbf0dd;
            }

            .list-row .amount.pending {
                color: #ff8e9d;
                text-transform: uppercase;
                letter-spacing: .04em;
            }

            .admin-note {
                margin: 8px 0 0;
                font-size: clamp(11px, 1.5vw, 12px);
                color: var(--muted);
            }

            .inline-deposit {
                border: 1px solid var(--line);
                border-radius: 12px;
                background: rgba(22, 31, 54, 0.7);
                padding: clamp(10px, 2vw, 12px);
                margin-top: 12px;
            }

            .inline-deposit h4 {
                margin: 0 0 8px;
                font-size: clamp(12px, 1.5vw, 13px);
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #cae3ff;
            }

            @media (min-width: 640px) {
                .admin-accordion summary {
                    grid-template-columns: 1.4fr auto auto auto;
                }

                .list-head,
                .list-row {
                    grid-template-columns: 1.6fr 1fr .8fr;
                    gap: 10px;
                    align-items: center;
                }

                .list-row {
                    display: grid;
                }

                .list-row>* {
                    flex: unset;
                    min-width: unset;
                }
            }

            @media (min-width: 1024px) {
                .compact-hero {
                    grid-template-columns: 1.5fr 1fr;
                }

                .smart-grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
            }
        </style>

        <section class="compact-hero">
            <div class="compact-intro">
                <div class="hero-kicker">SIPR GROUP</div>
                <h2>Welcome, {{ auth()->user()->name }}</h2>
                <p class="compact-meta">
                    <span>{{ auth()->user()->member_id }}</span>
                    <span>{{ $userModel::roleDisplayLabel($role) }}</span>
                    <span>{{ ucfirst(auth()->user()->status) }}</span>
                </p>

                <div class="status-pills">
                    <span class="pill">Unread Alerts: {{ $unreadCount }}</span>
                    <span class="pill">Due Month: {{ $dueSnapshot['month'] }}</span>
                    <span class="pill">Arrears: {{ $dueSnapshot['arrears_count'] }}</span>
                </div>

                <div class="compact-actions">
                    <a class="action-chip" href="{{ route('notifications.index') }}">View Notifications</a>
                    <a class="action-chip" href="{{ route('reports.index') }}">Open Reports</a>
                    <a class="action-chip" href="{{ route('noticeboard.index') }}">Noticeboard</a>
                </div>
            </div>

            <div class="compact-focus">
                <div class="focus-head">
                    <h3>Lifetime Savings/Deposited Amount</h3>
                    <span class="tone tone-ok">All time</span>
                </div>
                <div class="kpi" style="padding: 0; border: 0; background: transparent; box-shadow: none;">
                    <div class="value">{{ number_format($lifetimeDeposited, 2) }}</div>
                    <div class="note">Total deposits/savings recorded in your account since the beginning.</div>
                </div>
                <div class="mini-bar" aria-label="Due completion">
                    <div class="mini-fill" style="width: 100%"></div>
                </div>
                <p class="focus-note">
                    Lifetime invested amount: {{ number_format($lifetimeInvested, 2) }}.
                </p>
            </div>
        </section>

        @if ($isAdmin)
            <section class="panel">
                <h3 class="section-title">This Month Member Contributions</h3>
                <p class="admin-note">Expand each section to view member-wise totals for {{ $currentMonth }}.</p>

                <div class="inline-deposit">
                    <h4>Quick Add Deposit</h4>
                    <form method="POST" action="{{ route('transactions.store') }}" class="grid grid-4">
                        @csrf
                        <input type="hidden" name="type" value="deposit">

                        <select name="user_id" class="select" required>
                            <option value="">Select member</option>
                            @foreach ($membersForDeposit as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->member_id }})</option>
                            @endforeach
                        </select>

                        <input type="number" name="amount" min="0.01" step="0.01" class="input" placeholder="Deposit amount"
                            required>
                        <input type="date" name="date" value="{{ now()->toDateString() }}" class="input" required>
                        <button type="submit" class="primary-btn">Add Deposit</button>

                        <input type="text" name="note" class="input" placeholder="Note (optional)" style="grid-column:1 / -1">
                    </form>
                </div>

                <div class="accordion-stack">
                    @php
                        $sections = [
                            'deposit' => 'Deposits',
                        ];
                    @endphp

                    @foreach ($sections as $type => $label)
                        @php
                            $rows = $monthContribs[$type]['rows'] ?? collect();
                            $total = (float) ($monthContribs[$type]['total'] ?? 0);
                            $memberCount = $rows->count();
                        @endphp

                        <details class="admin-accordion" @if ($loop->first) open @endif>
                            <summary>
                                <span>{{ $label }}</span>
                                <span class="acc-pill">Members: {{ $memberCount }}</span>
                                <span class="acc-pill">Total: {{ number_format($total, 2) }}</span>
                                <span class="acc-toggle">Toggle</span>
                            </summary>

                            <div class="accordion-body">
                                @if ($memberCount > 0)
                                    <div class="list-head">
                                        <span>Member Name</span>
                                        <span>Member ID</span>
                                        <span style="text-align:right">Amount</span>
                                    </div>

                                    @foreach ($rows as $row)
                                        <div class="list-row">
                                            <span>{{ $row['member_name'] }}</span>
                                            <span>{{ $row['member_id'] }}</span>
                                            <span class="amount @if (($row['pending'] ?? false)) pending @endif">
                                                @if (($row['pending'] ?? false))
                                                    Pending
                                                @else
                                                    {{ number_format((float) $row['amount'], 2) }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="muted" style="margin: 2px 4px;">No {{ strtolower($label) }} recorded for this month.</p>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>

                <div style="margin-top: 14px; display: flex; gap: 8px;">
                    <a href="{{ route('monthly-payments.index') }}" class="primary-btn"
                        style="display: inline-block; padding: 10px 14px; font-size: 12px; font-weight: 800;">Show Monthly
                        Report</a>
                </div>
            </section>
        @endif

        <section class="panel">
            <h3 class="section-title">Essential Snapshot</h3>
            <div class="smart-grid" style="margin-top: 10px;">
                <div class="data-card">
                    <div class="label">Unread Notifications</div>
                    <div class="value">{{ $unreadCount }}</div>
                    <div class="note">Pending messages and approvals</div>
                </div>
                <div class="data-card">
                    <div class="label">Current Due Month</div>
                    <div class="value">{{ $dueSnapshot['month'] }}</div>
                    <div class="note">Contribution cycle in view</div>
                </div>
                <div class="data-card">
                    <div class="label">Outstanding (Current)</div>
                    <div class="value">{{ number_format($outstanding, 2) }}</div>
                    <div class="note">Amount left for this month</div>
                </div>
                <div class="data-card">
                    <div class="label">Arrears</div>
                    <div class="value">{{ $dueSnapshot['arrears_count'] }}</div>
                    <div class="note">Outstanding months ({{ number_format((float) $dueSnapshot['arrears_amount'], 2) }})
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Next Best Actions</h3>
            <div class="grid grid-3">
                <a class="kpi" href="{{ route('transactions.index') }}">
                    <div class="label">Update Contributions</div>
                    <div class="value">Transactions</div>
                    <div class="note">Record deposits, investments, expenses, and fines</div>
                </a>
                <a class="kpi" href="{{ route('wallets.index') }}">
                    <div class="label">Check Balances</div>
                    <div class="value">Wallets</div>
                    <div class="note">Review current passbook balances and movements</div>
                </a>
                <a class="kpi" href="{{ route('notifications.index') }}">
                    <div class="label">Resolve Alerts</div>
                    <div class="value">Notifications</div>
                    <div class="note">Read pending alerts, reminders, and approvals</div>
                </a>
            </div>
        </section>
    </div>
@endsection