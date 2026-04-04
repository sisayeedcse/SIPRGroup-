@extends('layouts.app')

@section('title', 'Transactions | SIPR')
@section('pageTitle', 'Transactions')
@section('pageSubtitle', 'Record and review deposits, investments, profits, expenses, and fines.')

@section('content')
    @php
        $canWrite = in_array((auth()->user()->role->value ?? auth()->user()->role), ['admin', 'finance'], true);
    @endphp

    <div class="page-stack">
        <section class="panel">
            <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-4">
                <select name="type" class="select">
                    <option value="">All types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
                <select name="user_id" class="select">
                    <option value="">All members</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((int) request('user_id') === $user->id)>{{ $user->name }} ({{ $user->member_id }})</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="input">
                <input type="date" name="to" value="{{ request('to') }}" class="input">
                <button type="submit" class="primary-btn">Filter</button>
            </form>
        </section>

        @if ($canWrite)
            <section class="panel">
                <h3 class="section-title">Create Transaction</h3>
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
                    <input type="text" name="note" placeholder="Note (optional)" class="input" style="grid-column:1 / -1">
                    <button type="submit" class="primary-btn" style="grid-column:1 / -1">Create</button>
                </form>
            </section>
        @endif

        <section class="panel">
            <h3 class="section-title">Transaction History</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th><th>Type</th><th>Member</th><th>Amount</th><th>Note</th><th>Open</th>@if ($canWrite)<th>Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            <tr>
                                <td>{{ $tx->date?->format('Y-m-d') }}</td>
                                <td><span class="pill pill-blue">{{ ucfirst($tx->type) }}</span></td>
                                <td>{{ $tx->user?->name }} ({{ $tx->user?->member_id }})</td>
                                <td>{{ number_format((float) $tx->amount, 2) }}</td>
                                <td>{{ $tx->note }}</td>
                                <td><a href="{{ route('transactions.show', $tx) }}">View</a></td>
                                @if ($canWrite)
                                    <td>
                                        <details>
                                            <summary class="pill pill-blue" style="cursor:pointer">Edit</summary>
                                            <div style="margin-top:10px" class="stack">
                                                <form method="POST" action="{{ route('transactions.update', $tx) }}" class="stack">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="user_id" class="select" required>
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->id }}" @selected($tx->user_id === $user->id)>{{ $user->name }} ({{ $user->member_id }})</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="type" class="select" required>
                                                        @foreach ($types as $type)
                                                            <option value="{{ $type }}" @selected($tx->type === $type)>{{ ucfirst($type) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="number" name="amount" min="0.01" step="0.01" value="{{ (float) $tx->amount }}" class="input" required>
                                                    <input type="date" name="date" value="{{ $tx->date?->format('Y-m-d') }}" class="input" required>
                                                    <input type="text" name="note" value="{{ $tx->note }}" class="input" placeholder="Note">
                                                    <button type="submit" class="soft-btn">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('transactions.destroy', $tx) }}" onsubmit="return confirm('Delete transaction?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-btn">Delete</button>
                                                </form>
                                            </div>
                                        </details>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canWrite ? 7 : 6 }}" class="empty">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $transactions->links() }}</div>
        </section>
    </div>
@endsection
