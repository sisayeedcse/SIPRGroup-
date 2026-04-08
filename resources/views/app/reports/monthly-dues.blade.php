@extends('layouts.app')

@section('title', 'Monthly Dues | SIPR')
@section('pageTitle', 'Monthly Dues Dashboard')
@section('pageSubtitle', 'Track paid, partial, and unpaid member contributions for each month.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $isAdmin = $role === 'admin';
    @endphp

    <div class="page-stack">
        <section class="panel">
            <div class="top-tools">
                <form method="GET" action="{{ route('reports.monthly-dues.index') }}" class="btn-row">
                    <input type="month" name="month" value="{{ $month }}" class="input" required>
                    <button type="submit" class="soft-btn">Load Month</button>
                </form>
                <div class="btn-row">
                    <a href="{{ route('reports.monthly-dues.csv', ['month' => $month]) }}" class="soft-btn">Download CSV</a>
                    <a href="{{ route('reports.index') }}" class="ghost-btn">Back to Reports</a>
                </div>
            </div>
        </section>

        <section class="grid grid-4">
            <div class="kpi">
                <div class="label">Members</div>
                <div class="value">{{ $memberCount }}</div>
                <div class="note">With due records this month</div>
            </div>
            <div class="kpi">
                <div class="label">Expected Total</div>
                <div class="value">{{ number_format($expectedTotal, 2) }}</div>
                <div class="note">Target collection amount</div>
            </div>
            <div class="kpi">
                <div class="label">Collected Total</div>
                <div class="value">{{ number_format($paidTotal, 2) }}</div>
                <div class="note">Paid against monthly target</div>
            </div>
            <div class="kpi">
                <div class="label">Collection Rate</div>
                <div class="value">{{ number_format($collectionRate, 2) }}%</div>
                <div class="note">Collected / expected</div>
            </div>
            <div class="kpi">
                <div class="label">Month Status</div>
                <div class="value">{{ $monthClosed ? 'Closed' : 'Open' }}</div>
                <div class="note">Posting for this month</div>
            </div>
        </section>

        <section class="grid grid-3">
            <div class="kpi">
                <div class="label">Paid</div>
                <div class="value">{{ $paidCount }}</div>
                <div class="note">Members fully paid</div>
            </div>
            <div class="kpi">
                <div class="label">Partial</div>
                <div class="value">{{ $partialCount }}</div>
                <div class="note">Members partially paid</div>
            </div>
            <div class="kpi">
                <div class="label">Unpaid</div>
                <div class="value">{{ $unpaidCount }}</div>
                <div class="note">Members unpaid this month</div>
            </div>
        </section>

        <section class="panel">
            <h3 class="section-title">Prepare Monthly Dues</h3>
            <p class="muted" style="margin-top:-4px">Set or refresh expected amount for all active members in this month.
            </p>
            <form method="POST" action="{{ route('reports.monthly-dues.prepare') }}" class="grid auto-fit"
                style="max-width:680px;margin-top:10px">
                @csrf
                <input type="month" name="month" value="{{ $month }}" class="input" required>
                <input type="number" name="expected_amount" min="0" step="0.01" class="input" placeholder="Expected amount"
                    required>
                <button type="submit" class="primary-btn">Prepare and Sync</button>
            </form>

            <form method="POST" action="{{ route('reports.monthly-dues.remind-unpaid') }}" class="btn-row"
                style="margin-top:10px">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="soft-btn">Notify Unpaid/Partial Members</button>
            </form>

            @if ($isAdmin)
                <form method="POST" action="{{ route('reports.monthly-dues.close-month') }}" class="stack"
                    style="margin-top:10px;max-width:680px">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="text" name="note" class="input" placeholder="Close note (optional)">
                    <button type="submit" class="danger-btn" @disabled($monthClosed)>
                        {{ $monthClosed ? 'Month Already Closed' : 'Close Month' }}
                    </button>
                </form>
            @endif
        </section>

        <section class="panel">
            <h3 class="section-title">Member Dues Status</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Member ID</th>
                            <th>Month</th>
                            <th>Expected</th>
                            <th>Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dues as $due)
                            <tr>
                                <td>{{ $due->user?->name }}</td>
                                <td>{{ $due->user?->member_id }}</td>
                                <td>{{ $due->due_month?->format('Y-m') }}</td>
                                <td>{{ number_format((float) $due->expected_amount, 2) }}</td>
                                <td>{{ number_format((float) $due->paid_amount, 2) }}</td>
                                <td>
                                    <span
                                        class="pill {{ $due->status === 'paid' ? 'pill-green' : ($due->status === 'partial' ? 'pill-blue' : 'pill-red') }}">
                                        {{ ucfirst($due->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">No monthly due records found for this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $dues->links() }}</div>
        </section>
    </div>
@endsection