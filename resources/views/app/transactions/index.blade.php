@extends('layouts.app')

@section('title', 'Transactions | SIPR')
@section('pageTitle', 'Transactions')
@section('pageSubtitle', 'Record and review deposits, investments, profits, expenses, and fines.')

@section('content')
    @php
        $role = auth()->user()->role->value ?? auth()->user()->role;
        $canWrite = in_array($role, ['admin', 'finance'], true);
        $isAdmin = $role === 'admin';
        $isMemberView = $isMember ?? ($role === 'member');
    @endphp

    <div class="page-stack">
        @if ($isMemberView)
            <section class="hero">
                <div class="hero-top">
                    <div>
                        <div class="hero-kicker">My Contribution</div>
                        <h2>My Invested Amount</h2>
                        <p>Your investment transactions recorded by admin/finance.</p>
                    </div>
                    <div class="kpi" style="min-width:min(100%,260px)">
                        <div class="label">Total Invested</div>
                        <div class="value">{{ number_format((float) ($investedTotal ?? 0), 2) }}</div>
                        <div class="note">Sum of your investment entries</div>
                    </div>
                </div>
            </section>
        @endif

        <section class="panel">
            <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-4">
                @if (! $isMemberView)
                    <select name="type" class="select">
                        <option value="">All types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    <select name="user_id" class="select">
                        <option value="">All members</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((int) request('user_id') === $user->id)>{{ $user->name }}
                                ({{ $user->member_id }})</option>
                        @endforeach
                    </select>
                @else
                    <div class="muted" style="grid-column: 1 / span 2; align-self: center;">Showing your investment records only.</div>
                @endif
                <input type="date" name="from" value="{{ request('from') }}" class="input">
                <input type="date" name="to" value="{{ request('to') }}" class="input">
                <button type="submit" class="primary-btn">Filter</button>
            </form>
        </section>

        @if ($canWrite)
            <section class="panel">
                <h3 class="section-title">Add Member Money (Cash Collection)</h3>
                <p class="muted" style="margin-top:-8px;margin-bottom:12px">Use this to record physical cash received from any member.</p>
                <form method="POST" action="{{ route('transactions.store') }}" class="grid grid-4">
                    @csrf
                    <select name="user_id" class="select" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->member_id }})</option>
                        @endforeach
                    </select>
                    <select name="type" class="select" required>
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="amount" min="0.01" step="0.01" placeholder="Amount" class="input" required>
                    <input type="date" name="date" value="{{ now()->toDateString() }}" class="input" required>
                    <input type="text" name="note" placeholder="Receipt note (optional)" class="input" style="grid-column:1 / -1">
                    <button type="submit" class="primary-btn" style="grid-column:1 / -1">Record Money Entry</button>
                </form>
            </section>
        @endif

        <section class="panel">
            <h3 class="section-title">Transaction History</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Open</th>@if ($canWrite)
                            <th>Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            <tr>
                                <td>{{ $tx->date?->format('Y-m-d') }}</td>
                                <td>
                                    <span class="pill pill-blue">{{ ucfirst($tx->type) }}</span>
                                    @if ($tx->is_adjustment)
                                        <span class="pill">Adjustment</span>
                                    @endif
                                    @if ($tx->requires_approval)
                                        <span
                                            class="pill {{ $tx->approval_status === 'approved' ? 'pill-green' : ($tx->approval_status === 'rejected' ? 'pill-red' : 'pill-blue') }}">
                                            {{ ucfirst((string) $tx->approval_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $tx->user?->name }} ({{ $tx->user?->member_id }})</td>
                                <td>{{ number_format((float) $tx->amount, 2) }}</td>
                                <td>{{ $tx->note }}</td>
                                <td><a href="{{ route('transactions.show', $tx) }}">View</a></td>
                                @if ($canWrite)
                                    <td>
                                        <details>
                                            <summary class="pill pill-blue" style="cursor:pointer">Adjust</summary>
                                            <div style="margin-top:10px" class="stack">
                                                <p class="muted" style="font-size:12px;margin:0 0 8px">Transactions are immutable. Create an adjustment to correct this entry.</p>
                                                <form method="POST" action="{{ route('transactions.adjust', $tx) }}" class="stack">
                                                    @csrf
                                                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                                                    <select name="type" class="select" required>
                                                        @foreach ($types as $type)
                                                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="number" name="amount" min="0.01" step="0.01" class="input"
                                                        placeholder="Adjustment amount" required>
                                                    <input type="text" name="note" class="input" placeholder="Adjustment note">
                                                    <button type="submit" class="ghost-btn">Create Adjustment</button>
                                                </form>

                                                @if ($isAdmin && $tx->is_adjustment && $tx->requires_approval && $tx->approval_status === 'pending')
                                                    <form method="POST" action="{{ route('transactions.adjustments.approve', $tx) }}"
                                                        class="stack" style="margin-top:6px">
                                                        @csrf
                                                        <input type="text" name="approval_note" class="input"
                                                            placeholder="Approval note (optional)">
                                                        <button type="submit" class="soft-btn">Approve Adjustment</button>
                                                    </form>

                                                    <form method="POST" action="{{ route('transactions.adjustments.reject', $tx) }}"
                                                        class="stack" style="margin-top:6px">
                                                        @csrf
                                                        <input type="text" name="approval_note" class="input"
                                                            placeholder="Rejection reason (optional)">
                                                        <button type="submit" class="danger-btn">Reject Adjustment</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </details>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canWrite ? 7 : 6 }}" class="empty">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $transactions->links() }}</div>
        </section>
    </div>
@endsection