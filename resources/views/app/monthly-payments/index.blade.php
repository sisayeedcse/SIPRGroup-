@extends('layouts.app')

@section('title', 'Monthly Payments | SIPR')
@section('pageTitle', 'Monthly Payments')
@section('pageSubtitle', 'Track and manage member deposits for each month.')

@section('content')
    <div class="page-stack">
        @php
            $role = auth()->user()->role->value ?? auth()->user()->role;
            $canWrite = in_array($role, ['admin', 'finance'], true);
        @endphp

        <style>
            .filter-section {
                display: flex;
                gap: 12px;
                align-items: flex-end;
                flex-wrap: wrap;
                margin-bottom: 16px;
            }

            .filter-form {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                width: 100%;
            }

            .filter-group {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .filter-group label {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                color: var(--muted);
                letter-spacing: .03em;
            }

            .filter-group select {
                padding: 8px 12px;
                border: 1px solid var(--line);
                border-radius: 6px;
                background: var(--surface);
                color: var(--text);
                font-size: 13px;
                min-width: 140px;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 10px;
                margin-bottom: 20px;
            }

            .stat-card {
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 12px;
                background: rgba(18, 27, 49, 0.5);
                text-align: center;
            }

            .stat-label {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                color: var(--muted);
                margin-bottom: 4px;
                letter-spacing: .03em;
            }

            .stat-value {
                font-size: 18px;
                font-weight: 800;
                color: var(--text);
            }

            .form-section {
                border: 1px solid var(--line);
                border-radius: 8px;
                padding: 14px;
                background: rgba(14, 21, 37, 0.8);
                margin-bottom: 16px;
            }

            .form-section h4 {
                margin: 0 0 12px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                color: #9cc3ff;
                letter-spacing: .03em;
            }

            .form-inline {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 10px;
                align-items: flex-end;
            }

            .form-group {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .form-group label {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                color: var(--muted);
                letter-spacing: .03em;
            }

            .form-group input,
            .form-group select {
                padding: 8px;
                border: 1px solid var(--line);
                border-radius: 6px;
                background: var(--surface);
                color: var(--text);
                font-size: 12px;
            }

            .submit-btn {
                padding: 8px 14px;
                background: linear-gradient(135deg, #3ecf8e 0%, #2da66d 100%);
                color: #fff;
                border: none;
                border-radius: 6px;
                font-weight: 700;
                font-size: 11px;
                text-transform: uppercase;
                cursor: pointer;
                transition: opacity .2s;
            }

            .submit-btn:hover {
                opacity: 0.9;
            }

            .table-scroll {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .deposits-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 16px;
                min-width: 760px;
            }

            .deposits-table thead {
                background: rgba(10, 18, 32, 0.8);
            }

            .deposits-table th {
                padding: 10px 12px;
                text-align: left;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: var(--muted);
                border-bottom: 1px solid var(--line);
            }

            .deposits-table td {
                padding: 10px 12px;
                border-bottom: 1px solid var(--line);
                font-size: 12px;
            }

            .deposits-table tbody tr {
                background: rgba(20, 28, 48, 0.6);
                transition: background .15s;
            }

            .deposits-table tbody tr:hover {
                background: rgba(25, 35, 58, 0.8);
            }

            .amount-paid {
                color: #bdf5df;
                font-weight: 700;
            }

            .amount-zero {
                color: #ffc4cc;
                font-weight: 700;
            }

            .member-name {
                font-weight: 700;
                color: #cae3ff;
            }

            .show-deposits {
                cursor: pointer;
                color: #91c5ff;
                font-weight: 700;
                font-size: 11px;
            }

            .deposit-list {
                margin-top: 10px;
                padding: 10px;
                background: rgba(0, 0, 0, 0.3);
                border-radius: 4px;
            }

            .deposit-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
                padding-bottom: 8px;
                border-bottom: 1px solid var(--line);
            }

            .deposit-item:last-child {
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
            }

            .deposit-amount {
                font-weight: 700;
            }

            .deposit-date {
                font-size: 10px;
                color: var(--muted);
            }

            .deposit-note {
                font-size: 11px;
                color: var(--muted);
                margin-top: 2px;
                word-break: break-word;
            }

            .remove-btn {
                padding: 4px 8px;
                background: rgba(255, 107, 127, 0.15);
                color: #ff8e9d;
                border: 1px solid rgba(255, 107, 127, 0.3);
                border-radius: 4px;
                font-size: 10px;
                font-weight: 700;
                cursor: pointer;
                transition: background .15s;
            }

            .remove-btn:hover {
                background: rgba(255, 107, 127, 0.25);
            }

            .empty-state {
                padding: 30px;
                text-align: center;
                color: var(--muted);
            }

            @media (max-width: 992px) {
                .stats-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .form-inline {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 768px) {
                .filter-section {
                    flex-direction: column;
                    align-items: stretch;
                }

                .filter-form {
                    flex-direction: column;
                }

                .filter-group select {
                    width: 100%;
                }

                .form-inline {
                    grid-template-columns: 1fr;
                }

                .submit-btn {
                    width: 100%;
                }

                .deposits-table {
                    min-width: 0;
                    font-size: 11px;
                }

                .deposits-table thead {
                    display: none;
                }

                .deposits-table,
                .deposits-table tbody,
                .deposits-table tr,
                .deposits-table td {
                    display: block;
                    width: 100%;
                }

                .deposits-table tbody tr {
                    border: 1px solid var(--line);
                    border-radius: 10px;
                    margin-bottom: 10px;
                    padding: 8px;
                }

                .deposits-table td {
                    border: none;
                    border-bottom: 1px solid var(--line);
                    padding: 8px 6px;
                    text-align: left;
                }

                .deposits-table td:last-child {
                    border-bottom: none;
                }

                .deposits-table td::before {
                    content: attr(data-label);
                    display: block;
                    font-size: 10px;
                    font-weight: 800;
                    color: var(--muted);
                    text-transform: uppercase;
                    letter-spacing: .04em;
                    margin-bottom: 2px;
                }

                .deposit-item {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }
        </style>

        <section class="panel">
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label>Year</label>
                        <select name="year" onchange="this.form.submit()">
                            @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Month</label>
                        <select name="month" onchange="this.form.submit()">
                            @foreach ($months as $monthKey => $monthLabel)
                                @if (str_starts_with($monthKey, $year))
                                    <option value="{{ $monthKey }}" @selected($month === $monthKey)>{{ $monthLabel }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Members</div>
                    <div class="stat-value">{{ $paymentRecords->count() }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Paid Members</div>
                    <div class="stat-value">{{ $stats['paid'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Paid</div>
                    <div class="stat-value">{{ number_format($paidTotal, 0) }}</div>
                </div>
            </div>
        </section>

        @if ($canWrite)
            <section class="panel">
                <div class="form-section">
                    <h4>Quick Add Deposit</h4>
                    <form method="POST" action="{{ route('monthly-payments.add-deposit') }}">
                        @csrf
                        <div class="form-inline">
                            <div class="form-group">
                                <label for="member_select">Member</label>
                                <select name="user_id" id="member_select" required>
                                    <option value="">Select member</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->member_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="amount_input">Amount</label>
                                <input type="number" name="amount" id="amount_input" min="0.01" step="0.01" placeholder="0.00"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="month_input">Month</label>
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="text" value="{{ $selectedDate->format('F Y') }}" disabled
                                    style="background: rgba(255,255,255,0.05);">
                            </div>
                            <button type="submit" class="submit-btn">Add Deposit</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <section class="panel">
            <h3 class="section-title">Member Deposits - {{ $selectedDate->format('F Y') }}</h3>

            @if ($paymentRecords->count() > 0)
                <div class="table-scroll">
                    <table class="deposits-table">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Member ID</th>
                                <th>Deposits Count</th>
                                <th>Total Paid</th>
                                @if ($canWrite)
                                    <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paymentRecords as $record)
                                <tr>
                                    <td data-label="Member Name" class="member-name">{{ $record['user_name'] }}</td>
                                    <td data-label="Member ID">{{ $record['user_member_id'] }}</td>
                                    <td data-label="Deposits Count">{{ $record['deposits']->count() }}</td>
                                    <td data-label="Total Paid"
                                        class="@if ($record['total_paid'] > 0) amount-paid @else amount-zero @endif">
                                        {{ number_format($record['total_paid'], 2) }}
                                    </td>
                                    @if ($canWrite)
                                        <td data-label="Action">
                                            @if ($record['deposits']->count() > 0)
                                                <details>
                                                    <summary class="show-deposits">Show Deposits</summary>
                                                    <div class="deposit-list">
                                                        @foreach ($record['deposits'] as $deposit)
                                                            <div class="deposit-item">
                                                                <div>
                                                                    <div class="deposit-amount">{{ number_format($deposit->amount, 2) }}</div>
                                                                    <div class="deposit-date">{{ $deposit->date->format('M d, Y') }}</div>
                                                                    @if ($deposit->note)
                                                                        <div class="deposit-note">{{ $deposit->note }}</div>
                                                                    @endif
                                                                </div>
                                                                <form method="POST"
                                                                    action="{{ route('monthly-payments.remove-deposit', $deposit->id) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="remove-btn"
                                                                        onclick="return confirm('Remove this deposit?')">Remove</button>
                                                                </form>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @else
                                                <span style="color: var(--muted); font-size: 11px;">-</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canWrite ? 5 : 4 }}" class="empty-state">
                                        No members found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    No payment records for {{ $selectedDate->format('F Y') }}.
                </div>
            @endif
        </section>
    </div>
@endsection