@extends('layouts.app')

@section('title', 'Wallets | SIPR')
@section('pageTitle', 'Wallet Overview')
@section('pageSubtitle', 'Track available balances and review passbook history.')

@section('content')
    <div class="page-stack">
        <section class="panel">
            <div class="top-tools">
                @if ($canViewAllWallets)
                    <div class="muted">Select a member to preview their passbook and balances.</div>
                    <form method="GET" action="{{ route('wallets.index') }}"
                        style="display: grid; grid-template-columns: 1fr auto; gap: clamp(8px, 2vw, 10px); width: 100%; align-items: center;">
                        <select name="user_id" class="select">
                            <option value="">Select member</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((int) request('user_id') === $user->id)>{{ $user->name }}
                                    ({{ $user->member_id }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="primary-btn" style="width: auto; min-width: 44px;">View</button>
                    </form>
                @else
                    <div class="muted">You can view only your own wallet and passbook.</div>
                @endif
            </div>
        </section>

        @if ($selectedUser)
            <section class="hero">
                <div class="hero-top">
                    <div style="flex: 1; min-width: 0;">
                        <div class="hero-kicker">Member Passbook</div>
                        <h2>{{ $selectedUser->name }}</h2>
                        <p>{{ $selectedUser->member_id }}</p>
                    </div>
                    <div class="grid grid-2" style="min-width: min(100%, 420px); flex-shrink: 0;">
                        <div class="kpi">
                            <div class="label">Savings Total</div>
                            <div class="value">{{ number_format((float) ($financialSummary['savings_total'] ?? 0), 2) }}</div>
                        </div>
                        <div class="kpi">
                            <div class="label">Invested Total</div>
                            <div class="value">{{ number_format((float) ($financialSummary['invested_total'] ?? 0), 2) }}</div>
                        </div>
                        <div class="kpi">
                            <div class="label">Available</div>
                            <div class="value">{{ number_format((float) ($selectedUser->wallet->available ?? 0), 2) }}</div>
                        </div>
                        <div class="kpi">
                            <div class="label">Locked</div>
                            <div class="value">{{ number_format((float) ($selectedUser->wallet->locked ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel">
                <h3 class="section-title">Recent History</h3>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Label</th>
                                <th>Amount</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentHistory as $entry)
                                <tr>
                                    <td>{{ $entry->date?->format('Y-m-d') }}</td>
                                    <td><span class="pill pill-blue">{{ ucfirst($entry->type) }}</span></td>
                                    <td>{{ $entry->label }}</td>
                                    <td>{{ number_format((float) $entry->amount, 2) }}</td>
                                    <td>{{ $entry->note }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty">No wallet history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="panel">
            <h3 class="section-title">{{ $canViewAllWallets ? 'All Wallet Balances' : 'Your Wallet Balance' }}</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Available</th>
                            <th>Locked</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($wallets as $wallet)
                            <tr>
                                <td>{{ $wallet->user?->name }} ({{ $wallet->user?->member_id }})</td>
                                <td>{{ number_format((float) $wallet->available, 2) }}</td>
                                <td>{{ number_format((float) $wallet->locked, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty">No wallets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px">{{ $wallets->links() }}</div>
        </section>
    </div>
@endsection